<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SyncLog;

class SyncTenantData extends Command
{
    protected $signature = 'tenant:sync-comet {tenant_id?} {--all}';
    protected $description = 'Sync Comet data from landlord to tenant database(s)';

    public function handle()
    {
        // Start sync log
        $syncLog = SyncLog::startSync('tenant_sync');

        try {
            require_once base_path('lib/sync_helpers.php');

            $tenantId = $this->argument('tenant_id');
            $syncAll = $this->option('all');

        // Get tenants to sync
        $tenants = [];

        if ($syncAll) {
            $this->info('🔄 Syncing ALL tenant databases...');
            $tenants = DB::connection('mysql')->table('tenants')->get();
        } elseif ($tenantId) {
            $this->info("🔄 Syncing tenant: {$tenantId}");
            $tenant = DB::connection('mysql')->table('tenants')->where('id', $tenantId)->first();
            if (!$tenant) {
                $this->error("Tenant '{$tenantId}' not found!");
                return 1;
            }
            $tenants = [$tenant];
        } else {
            $this->error('Please specify a tenant ID or use --all flag');
            $this->line('Usage: php artisan tenant:sync-comet <tenant_id>');
            $this->line('   or: php artisan tenant:sync-comet --all');
            return 1;
        }

        $this->newLine();
        $bar = $this->output->createProgressBar(count($tenants));
        $bar->start();

        $totalStats = [
            'tenants_synced' => 0,
            'tenants_failed' => 0,
            'total_matches' => 0,
            'total_rankings' => 0,
            'total_scorers' => 0,
            'total_events' => 0,
            'total_match_players' => 0,
            'total_match_officials' => 0,
            'total_match_team_officials' => 0,
            'total_match_phases' => 0,
            'total_own_goals' => 0,
            'total_competitions' => 0,
        ];

        foreach ($tenants as $tenant) {
            $bar->advance();

            $tenantData = json_decode($tenant->data, true);
            $tenantDbName = $tenantData['tenancy_db_name'] ?? null;

            if (!$tenantDbName) {
                $totalStats['tenants_failed']++;
                continue;
            }

            try {
                $stats = $this->syncTenantData($tenant->id, $tenantDbName);
                $totalStats['tenants_synced']++;
                $totalStats['total_matches'] += $stats['matches'];
                $totalStats['total_rankings'] += $stats['rankings'];
                $totalStats['total_scorers'] += $stats['scorers'];
                $totalStats['total_events'] += $stats['events'];
                $totalStats['total_match_players'] += $stats['match_players'];
                $totalStats['total_match_officials'] += $stats['match_officials'];
                $totalStats['total_match_team_officials'] += $stats['match_team_officials'];
                $totalStats['total_match_phases'] += $stats['match_phases'];
                $totalStats['total_own_goals'] += $stats['own_goals'];
                $totalStats['total_competitions'] += $stats['club_competitions'];
            } catch (\Exception $e) {
                $totalStats['tenants_failed']++;
                $this->newLine();
                $this->error("Failed to sync {$tenant->name}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ TENANT SYNC COMPLETED');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Tenants synced', $totalStats['tenants_synced']],
                ['Tenants failed', $totalStats['tenants_failed']],
                ['Total matches synced', $totalStats['total_matches']],
                ['Total rankings synced', $totalStats['total_rankings']],
                ['Total scorers synced', $totalStats['total_scorers']],
                ['Total match events synced', $totalStats['total_events']],
                ['Total match players synced', $totalStats['total_match_players']],
                ['Total match officials synced', $totalStats['total_match_officials']],
                ['Total match team officials synced', $totalStats['total_match_team_officials']],
                ['Total match phases synced', $totalStats['total_match_phases']],
                ['Total own goals synced', $totalStats['total_own_goals']],
                ['Total competitions synced', $totalStats['total_competitions']],
            ]
        );

        // Complete sync log
        $syncLog->complete([
            'inserted' => 0, // We don't track individual inserts for tenant sync
            'updated' => 0,
            'skipped' => 0,
            'failed' => $totalStats['tenants_failed'],
            'total' => count($tenants),
            'status' => $totalStats['tenants_failed'] > 0 ? 'partial' : 'success',
            'metadata' => [
                'tenants_synced' => $totalStats['tenants_synced'],
                'total_matches' => $totalStats['total_matches'],
                'total_rankings' => $totalStats['total_rankings'],
                'total_scorers' => $totalStats['total_scorers'],
                'total_events' => $totalStats['total_events'],
            ],
        ]);

        return 0;

        } catch (\Exception $e) {
            $syncLog->fail($e->getMessage(), $e->getTraceAsString());
            $this->error('Sync failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function syncTenantData($tenantId, $tenantDbName)
    {
        // Get tenant's team FIFA ID from tenant name or data
        $landlord = new \mysqli('localhost', 'root', '', 'kpkb3');
        $tenant = new \mysqli('localhost', 'root', '', $tenantDbName);

        if ($landlord->connect_error || $tenant->connect_error) {
            throw new \Exception("Database connection failed");
        }

    // Determine the team FIFA ID for this tenant
    // Prefer reading from tenant template_settings; fallback to static mapping
    $teamFifaId = $this->getTeamFifaIdForTenant($tenantId, $landlord, $tenant);

        if (!$teamFifaId) {
            $landlord->close();
            $tenant->close();
            return [
                'matches' => 0,
                'rankings' => 0,
                'scorers' => 0,
                'events' => 0,
                'match_players' => 0,
                'match_officials' => 0,
                'match_team_officials' => 0,
                'match_phases' => 0,
                'own_goals' => 0,
                'club_competitions' => 0,
            ];
        }

        $stats = [
            'matches' => 0,
            'rankings' => 0,
            'scorers' => 0,
            'events' => 0,
            'match_players' => 0,
            'match_officials' => 0,
            'match_team_officials' => 0,
            'match_phases' => 0,
            'own_goals' => 0,
            'club_competitions' => 0,
        ];

        // Sync Matches
        $matchesQuery = "
            SELECT * FROM comet_matches
            WHERE team_fifa_id_home = $teamFifaId OR team_fifa_id_away = $teamFifaId
        ";
        $matchesResult = $landlord->query($matchesQuery);

        $matchIds = [];

        while ($match = $matchesResult->fetch_assoc()) {
            unset($match['id']);
            $keyCols = ['match_fifa_id' => $match['match_fifa_id']];
            try {
                upsert_if_changed($tenant, 'comet_matches', $keyCols, $match);
                $stats['matches']++;
                if (!empty($match['match_fifa_id'])) {
                    $matchIds[] = (int)$match['match_fifa_id'];
                }
            } catch (\Exception $e) {
                // Skip errors
            }
        }

        // Sync Rankings - all teams from competitions where this team plays
        $competitionsQuery = "
            SELECT DISTINCT competition_fifa_id
            FROM comet_rankings
            WHERE team_fifa_id = $teamFifaId
        ";
        $competitionsResult = $landlord->query($competitionsQuery);
        $competitionIds = [];

        while ($row = $competitionsResult->fetch_assoc()) {
            $competitionIds[] = $row['competition_fifa_id'];
        }

        if (!empty($competitionIds)) {
            $competitionIdsList = implode(',', $competitionIds);
            $rankingsQuery = "
                SELECT * FROM comet_rankings
                WHERE competition_fifa_id IN ($competitionIdsList)
            ";
            $rankingsResult = $landlord->query($rankingsQuery);

            while ($ranking = $rankingsResult->fetch_assoc()) {
                unset($ranking['id']);
                $keyCols = [
                    'competition_fifa_id' => $ranking['competition_fifa_id'],
                    'team_fifa_id' => $ranking['team_fifa_id']
                ];
                try {
                    upsert_if_changed($tenant, 'comet_rankings', $keyCols, $ranking);
                    $stats['rankings']++;
                } catch (\Exception $e) {
                    // Skip errors
                }
            }
        }

        // Sync Top Scorers
        $scorersQuery = "
            SELECT * FROM comet_top_scorers
            WHERE club_id = $teamFifaId
        ";
        $scorersResult = $landlord->query($scorersQuery);

        while ($scorer = $scorersResult->fetch_assoc()) {
            unset($scorer['id']);
            $keyCols = [
                'competition_fifa_id' => $scorer['competition_fifa_id'],
                'player_fifa_id' => $scorer['player_fifa_id']
            ];
            try {
                upsert_if_changed($tenant, 'comet_top_scorers', $keyCols, $scorer);
                $stats['scorers']++;
            } catch (\Exception $e) {
                // Skip errors
            }
        }

        // Sync Own Goal Scorers (by competitions)
        if (!empty($competitionIds)) {
            $competitionIdsList = implode(',', $competitionIds);
            $ownGoalsQuery = "
                SELECT * FROM comet_own_goal_scorers
                WHERE competition_fifa_id IN ($competitionIdsList)
            ";
            $ownGoalsResult = $landlord->query($ownGoalsQuery);

            if ($ownGoalsResult) {
                while ($row = $ownGoalsResult->fetch_assoc()) {
                    unset($row['id']);
                    $keyCols = [
                        'competition_fifa_id' => $row['competition_fifa_id'],
                        'player_fifa_id' => $row['player_fifa_id'],
                        'team_fifa_id' => $row['team_fifa_id'] ?? null,
                    ];
                    try {
                        upsert_if_changed($tenant, 'comet_own_goal_scorers', $keyCols, $row);
                        $stats['own_goals']++;
                    } catch (\Exception $e) {
                        // Skip errors
                    }
                }
            }
        }

        // Sync Competition details (club_competitions) for competitions this club plays
        if (!empty($competitionIds)) {
            $competitionIdsList = implode(',', $competitionIds);
            $clubCompsQuery = "
                SELECT * FROM comet_club_competitions
                WHERE competitionFifaId IN ($competitionIdsList)
            ";
            $clubCompsResult = $landlord->query($clubCompsQuery);

            if ($clubCompsResult) {
                while ($row = $clubCompsResult->fetch_assoc()) {
                    unset($row['id']);
                    // No clear unique PK besides competitionFifaId; use that
                    $keyCols = [ 'competitionFifaId' => $row['competitionFifaId'] ];
                    try {
                        upsert_if_changed($tenant, 'comet_club_competitions', $keyCols, $row);
                        $stats['club_competitions']++;
                    } catch (\Exception $e) {
                        // Skip errors
                    }
                }
            }
        }

        // Sync Match-related details if we have matches
        if (!empty($matchIds)) {
            $matchIdsList = implode(',', array_unique($matchIds));

            // 1) Match Events
            $eventsQuery = "SELECT * FROM comet_match_events WHERE match_fifa_id IN ($matchIdsList)";
            $eventsResult = $landlord->query($eventsQuery);
            if ($eventsResult) {
                while ($row = $eventsResult->fetch_assoc()) {
                    unset($row['id']);
                    $keyCols = ['match_event_fifa_id' => $row['match_event_fifa_id']];
                    try {
                        upsert_if_changed($tenant, 'comet_match_events', $keyCols, $row);
                        $stats['events']++;
                    } catch (\Exception $e) {
                        // Skip errors
                    }
                }
            }

            // 2) Match Players
            $playersQuery = "SELECT * FROM comet_match_players WHERE match_fifa_id IN ($matchIdsList)";
            $playersResult = $landlord->query($playersQuery);
            if ($playersResult) {
                while ($row = $playersResult->fetch_assoc()) {
                    unset($row['id']);
                    $keyCols = [
                        'match_fifa_id' => $row['match_fifa_id'],
                        'person_fifa_id' => $row['person_fifa_id']
                    ];
                    try {
                        upsert_if_changed($tenant, 'comet_match_players', $keyCols, $row);
                        $stats['match_players']++;
                    } catch (\Exception $e) {
                        // Skip errors
                    }
                }
            }

            // 3) Match Officials
            $officialsQuery = "SELECT * FROM comet_match_officials WHERE match_fifa_id IN ($matchIdsList)";
            $officialsResult = $landlord->query($officialsQuery);
            if ($officialsResult) {
                while ($row = $officialsResult->fetch_assoc()) {
                    unset($row['id']);
                    $keyCols = [
                        'match_fifa_id' => $row['match_fifa_id'],
                        'person_fifa_id' => $row['person_fifa_id'],
                        'role' => $row['role'] ?? ''
                    ];
                    try {
                        upsert_if_changed($tenant, 'comet_match_officials', $keyCols, $row);
                        $stats['match_officials']++;
                    } catch (\Exception $e) {
                        // Skip errors
                    }
                }
            }

            // 4) Match Team Officials
            $mtoQuery = "SELECT * FROM comet_match_team_officials WHERE match_fifa_id IN ($matchIdsList)";
            $mtoResult = $landlord->query($mtoQuery);
            if ($mtoResult) {
                while ($row = $mtoResult->fetch_assoc()) {
                    unset($row['id']);
                    $keyCols = [
                        'match_fifa_id' => $row['match_fifa_id'],
                        'team_fifa_id' => $row['team_fifa_id'],
                        'person_fifa_id' => $row['person_fifa_id']
                    ];
                    try {
                        upsert_if_changed($tenant, 'comet_match_team_officials', $keyCols, $row);
                        $stats['match_team_officials']++;
                    } catch (\Exception $e) {
                        // Skip errors
                    }
                }
            }

            // 5) Match Phases
            $phasesQuery = "SELECT * FROM comet_match_phases WHERE match_fifa_id IN ($matchIdsList)";
            $phasesResult = $landlord->query($phasesQuery);
            if ($phasesResult) {
                while ($row = $phasesResult->fetch_assoc()) {
                    unset($row['id']);
                    $keyCols = [
                        'match_fifa_id' => $row['match_fifa_id'],
                        'phase' => $row['phase'] ?? ($row['phase_name'] ?? '')
                    ];
                    try {
                        upsert_if_changed($tenant, 'comet_match_phases', $keyCols, $row);
                        $stats['match_phases']++;
                    } catch (\Exception $e) {
                        // Skip errors
                    }
                }
            }
        }

        $landlord->close();
        $tenant->close();

        return $stats;
    }

    private function getTeamFifaIdForTenant($tenantId, $landlord, $tenant)
    {
        // 1) Try to read from tenant template settings
        try {
            $res = $tenant->query("SELECT club_fifa_id FROM template_settings LIMIT 1");
            if ($res && ($row = $res->fetch_assoc())) {
                $val = (int)($row['club_fifa_id'] ?? 0);
                if ($val > 0) return $val;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 2) Fallback: static mapping
        $mapping = [
            'nkprigorjem' => 598,  // NK Prigorje Markuševec
            'nknapijed' => 396,
        ];

        return $mapping[$tenantId] ?? null;
    }
}
