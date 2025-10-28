<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Contracts\Tenant;

class SyncCometDataToTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TenantWithDatabase&Tenant $tenant
    ) {}

    public function handle(): void
    {
        try {
            Log::info("Starting COMET data sync for tenant {$this->tenant->id}");
            tenancy()->initialize($this->tenant);

            $clubFifaId = DB::table('comet_clubs_extended')->value('club_fifa_id');
            if (!$clubFifaId) {
                Log::warning("No club_fifa_id found for tenant {$this->tenant->id}");
                return;
            }

            Log::info("Syncing for club FIFA ID: {$clubFifaId}");
            $competitionIds = $this->getCompetitionIds($clubFifaId);
            Log::info("Found " . count($competitionIds) . " competitions");

            $this->syncMatches($competitionIds);
            $this->syncRankings($competitionIds);
            $this->syncTopScorers($competitionIds);
            $this->syncOwnGoalScorers($competitionIds);
            $this->syncCompetitionDetails($competitionIds);
            $this->syncClubData($clubFifaId);
            $this->syncMatchDetails();

            Log::info("COMET sync completed for tenant {$this->tenant->id}");
        } catch (\Exception $e) {
            Log::error("Failed to sync COMET data: " . $e->getMessage());
            throw $e;
        } finally {
            tenancy()->end();
        }
    }

    private function getCompetitionIds(int $clubFifaId): array
    {
        return DB::connection('central')
            ->table('comet_matches')
            ->where(function($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->distinct()
            ->pluck('competition_fifa_id')
            ->toArray();
    }

    private function syncMatches(array $competitionIds): void
    {
        DB::table('comet_matches')->truncate();
        $matches = DB::connection('central')
            ->table('comet_matches')
            ->whereIn('competition_fifa_id', $competitionIds)
            ->get();

        $count = 0;
        foreach ($matches->chunk(100) as $chunk) {
            $data = $chunk->map(fn($record) => (array) $record)->toArray();
            DB::table('comet_matches')->insert($data);
            $count += count($data);
        }
        Log::info("Synced {$count} matches");
    }

    private function syncRankings(array $competitionIds): void
    {
        DB::table('comet_rankings')->truncate();
        $rankings = DB::connection('central')
            ->table('comet_rankings')
            ->whereIn('competition_fifa_id', $competitionIds)
            ->get();

        $count = 0;
        foreach ($rankings->chunk(100) as $chunk) {
            $data = $chunk->map(fn($record) => (array) $record)->toArray();
            DB::table('comet_rankings')->insert($data);
            $count += count($data);
        }
        Log::info("Synced {$count} rankings");
    }

    private function syncTopScorers(array $competitionIds): void
    {
        DB::table('comet_top_scorers')->truncate();
        $topScorers = DB::connection('central')
            ->table('comet_top_scorers')
            ->whereIn('competition_fifa_id', $competitionIds)
            ->get();

        $count = 0;
        foreach ($topScorers->chunk(100) as $chunk) {
            $data = $chunk->map(fn($record) => (array) $record)->toArray();
            DB::table('comet_top_scorers')->insert($data);
            $count += count($data);
        }
        Log::info("Synced {$count} top scorers");
    }

    private function syncOwnGoalScorers(array $competitionIds): void
    {
        DB::table('comet_own_goal_scorers')->truncate();
        $ownGoalScorers = DB::connection('central')
            ->table('comet_own_goal_scorers')
            ->whereIn('competition_fifa_id', $competitionIds)
            ->get();

        if ($ownGoalScorers->count() > 0) {
            $count = 0;
            foreach ($ownGoalScorers->chunk(100) as $chunk) {
                $data = $chunk->map(fn($record) => (array) $record)->toArray();
                DB::table('comet_own_goal_scorers')->insert($data);
                $count += count($data);
            }
            Log::info("Synced {$count} own goal scorers");
        }
    }

    private function syncCompetitionDetails(array $competitionIds): void
    {
        DB::table('comet_club_competitions')->truncate();
        $competitions = DB::connection('central')
            ->table('comet_club_competitions')
            ->whereIn('competitionFifaId', $competitionIds)
            ->whereIn('id', function($query) use ($competitionIds) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('comet_club_competitions')
                    ->whereIn('competitionFifaId', $competitionIds)
                    ->groupBy('competitionFifaId');
            })
            ->get();

        $count = 0;
        foreach ($competitions->chunk(100) as $chunk) {
            $data = $chunk->map(fn($record) => (array) $record)->toArray();
            DB::table('comet_club_competitions')->insert($data);
            $count += count($data);
        }
        Log::info("Synced {$count} competition details");
    }

    private function syncClubData(int $clubFifaId): void
    {
        $tables = [
            'comet_club_representatives' => ['club_fifa_id'],
            'comet_coaches' => ['club_fifa_id'],
            'comet_team_officials' => ['club_fifa_id'],
        ];

        foreach ($tables as $tableName => $filterColumns) {
            $exists = DB::connection('central')
                ->select("SHOW TABLES LIKE '{$tableName}'");
            if (empty($exists)) continue;

            DB::table($tableName)->truncate();
            $query = DB::connection('central')->table($tableName);
            foreach ($filterColumns as $column) {
                $query->orWhere($column, $clubFifaId);
            }
            $records = $query->get();

            if ($records->count() > 0) {
                $count = 0;
                foreach ($records->chunk(100) as $chunk) {
                    $data = $chunk->map(fn($record) => (array) $record)->toArray();
                    DB::table($tableName)->insert($data);
                    $count += count($data);
                }
                Log::info("Synced {$count} records from {$tableName}");
            }
        }
    }

    private function syncMatchDetails(): void
    {
        $matchIds = DB::table('comet_matches')->pluck('match_fifa_id')->toArray();
        if (empty($matchIds)) {
            Log::info("No matches to sync details for");
            return;
        }

        $matchTeams = DB::table('comet_matches')
            ->select('match_fifa_id', 'team_fifa_id_home', 'team_fifa_id_away')
            ->whereIn('match_fifa_id', $matchIds)
            ->get()
            ->keyBy('match_fifa_id')
            ->toArray();

        $tables = [
            'comet_match_events' => 'match_fifa_id',
            'comet_match_players' => 'match_fifa_id',
            'comet_match_officials' => 'match_fifa_id',
            'comet_match_phases' => 'match_fifa_id',
            'comet_match_team_officials' => 'match_fifa_id',
        ];

        foreach ($tables as $tableName => $foreignKey) {
            $exists = DB::connection('central')->select("SHOW TABLES LIKE '{$tableName}'");
            if (empty($exists)) continue;

            DB::table($tableName)->truncate();
            $records = DB::connection('central')
                ->table($tableName)
                ->whereIn($foreignKey, $matchIds)
                ->get();

            if ($records->count() > 0) {
                $count = 0;
                foreach ($records->chunk(100) as $chunk) {
                    $data = $chunk->map(function($record) use ($tableName, $matchTeams) {
                        $arr = (array) $record;

                        if ($tableName === 'comet_match_players') {
                            if (isset($arr['team_nature']) && $arr['team_nature'] === '') {
                                $matchId = $arr['match_fifa_id'] ?? null;
                                $teamId = $arr['team_fifa_id'] ?? null;
                                if ($matchId && $teamId && isset($matchTeams[$matchId])) {
                                    $match = $matchTeams[$matchId];
                                    if ($teamId == $match->team_fifa_id_home) {
                                        $arr['team_nature'] = 'HOME';
                                    } elseif ($teamId == $match->team_fifa_id_away) {
                                        $arr['team_nature'] = 'AWAY';
                                    } else return null;
                                } else return null;
                            }
                            foreach ($arr as $k => $v) {
                                if ($v === '' && $k !== 'team_nature') $arr[$k] = null;
                            }
                        } elseif ($tableName === 'comet_match_events') {
                            if (isset($arr['event_type']) && $arr['event_type'] === '') return null;
                            foreach ($arr as $k => $v) {
                                if ($v === '' && $k !== 'event_type') $arr[$k] = null;
                            }
                        } else {
                            foreach ($arr as $k => $v) {
                                if ($v === '') $arr[$k] = null;
                            }
                        }
                        return $arr;
                    })->filter()->toArray();

                    if (count($data) > 0) {
                        DB::table($tableName)->insert($data);
                        $count += count($data);
                    }
                }
                Log::info("Synced {$count} records from {$tableName}");
            }
        }
    }

    public function tags(): array
    {
        return ['tenant:' . $this->tenant->id, 'tenant-setup', 'comet-sync'];
    }
}
