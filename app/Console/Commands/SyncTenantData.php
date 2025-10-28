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

        // Try to find the team FIFA ID from the tenant name
        // This is a simplified approach - you might need to store this in tenant metadata
        $teamFifaId = $this->getTeamFifaIdForTenant($tenantId, $landlord);

        if (!$teamFifaId) {
            $landlord->close();
            $tenant->close();
            return ['matches' => 0, 'rankings' => 0, 'scorers' => 0];
        }

        $stats = ['matches' => 0, 'rankings' => 0, 'scorers' => 0];

        // Sync Matches
        $matchesQuery = "
            SELECT * FROM comet_matches
            WHERE team_fifa_id_home = $teamFifaId OR team_fifa_id_away = $teamFifaId
        ";
        $matchesResult = $landlord->query($matchesQuery);

        while ($match = $matchesResult->fetch_assoc()) {
            unset($match['id']);
            $keyCols = ['match_fifa_id' => $match['match_fifa_id']];
            try {
                upsert_if_changed($tenant, 'comet_matches', $keyCols, $match);
                $stats['matches']++;
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

        $landlord->close();
        $tenant->close();

        return $stats;
    }

    private function getTeamFifaIdForTenant($tenantId, $landlord)
    {
        // Map tenant IDs to team FIFA IDs
        // TODO: This should be stored in tenant metadata or a mapping table
        $mapping = [
            'nkprigorjem' => 598,  // NK Prigorje Markuševec
            // Add more mappings here as needed
        ];

        return $mapping[$tenantId] ?? null;
    }
}
