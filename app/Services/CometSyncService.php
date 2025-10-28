<?php

namespace App\Services;

use App\Models\SyncLog;
use App\Models\Comet\CometMatch;
use App\Models\Comet\CometRanking;
use App\Models\Comet\CometTopScorer;
use App\Models\Comet\CometClubCompetition;
use App\Models\Comet\CometMatchEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CometSyncService
{
    public function __construct(
        private CometApiService $apiService
    ) {}

    /**
     * Sync Result DTO
     */
    public function createResult(array $stats): array
    {
        return [
            'success' => $stats['errors'] === 0,
            'inserted' => $stats['inserted'] ?? 0,
            'updated' => $stats['updated'] ?? 0,
            'skipped' => $stats['skipped'] ?? 0,
            'errors' => $stats['errors'] ?? 0,
            'total' => $stats['total'] ?? 0,
            'processed' => $stats['processed'] ?? 0,
            'metadata' => $stats['metadata'] ?? [],
        ];
    }

    /**
     * Sync all matches from Comet API
     */
    public function syncMatches(array $options = []): array
    {
        $stats = [
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total' => 0,
            'processed' => 0,
        ];

        // Get active competitions
        $competitions = CometClubCompetition::where('status', 'active')->get();
        $stats['processed'] = $competitions->count();

        // Load team logos
        $logoMap = $this->loadTeamLogos();

        foreach ($competitions as $competition) {
            try {
                $matches = $this->apiService->getCompetitionMatches($competition->competitionFifaId);

                foreach ($matches as $matchData) {
                    $result = $this->upsertMatch($matchData, $competition, $logoMap);
                    $stats[$result]++;
                    $stats['total']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("Failed to sync matches for competition {$competition->competitionFifaId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->createResult($stats);
    }

    /**
     * Sync all rankings from Comet API
     */
    public function syncRankings(array $options = []): array
    {
        $stats = [
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total' => 0,
            'processed' => 0,
        ];

        $competitions = CometClubCompetition::where('status', 'active')->get();
        $stats['processed'] = $competitions->count();

        $logoMap = $this->loadTeamLogos();

        foreach ($competitions as $competition) {
            try {
                $rankings = $this->apiService->getCompetitionRanking($competition->competitionFifaId);

                foreach ($rankings as $rankingData) {
                    $result = $this->upsertRanking($rankingData, $competition, $logoMap);
                    $stats[$result]++;
                    $stats['total']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("Failed to sync rankings for competition {$competition->competitionFifaId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->createResult($stats);
    }

    /**
     * Sync all top scorers from Comet API
     */
    public function syncTopScorers(array $options = []): array
    {
        $stats = [
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total' => 0,
            'processed' => 0,
        ];

        $competitions = CometClubCompetition::where('status', 'active')->get();
        $stats['processed'] = $competitions->count();

        foreach ($competitions as $competition) {
            try {
                $topScorers = $this->apiService->getCompetitionTopScorers($competition->competitionFifaId);

                foreach ($topScorers as $scorerData) {
                    $result = $this->upsertTopScorer($scorerData, $competition);
                    $stats[$result]++;
                    $stats['total']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("Failed to sync top scorers for competition {$competition->competitionFifaId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->createResult($stats);
    }

    /**
     * Sync all data types
     */
    public function syncAll(): array
    {
        $results = [];

        $results['matches'] = $this->syncMatches();
        $results['rankings'] = $this->syncRankings();
        $results['topScorers'] = $this->syncTopScorers();

        $totalStats = [
            'inserted' => array_sum(array_column($results, 'inserted')),
            'updated' => array_sum(array_column($results, 'updated')),
            'skipped' => array_sum(array_column($results, 'skipped')),
            'errors' => array_sum(array_column($results, 'errors')),
            'total' => array_sum(array_column($results, 'total')),
            'processed' => 0,
            'metadata' => [
                'synced_types' => ['matches', 'rankings', 'topScorers'],
                'details' => $results,
            ],
        ];

        return $this->createResult($totalStats);
    }

    /**
     * Load team logos from directory
     */
    private function loadTeamLogos(): array
    {
        $logoDir = public_path('images/kp_team_logo_images');
        $logoMap = [];

        if (!is_dir($logoDir)) {
            return $logoMap;
        }

        $logoFiles = glob($logoDir . '/*');

        foreach ($logoFiles as $logoFile) {
            $filename = basename($logoFile);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $teamId = pathinfo($filename, PATHINFO_FILENAME);

            if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) {
                // Prefer PNG files
                if (!isset($logoMap[$teamId]) || $extension === 'png') {
                    $logoMap[$teamId] = '/images/kp_team_logo_images/' . $filename;
                }
            }
        }

        return $logoMap;
    }

    /**
     * Upsert a match record
     */
    private function upsertMatch(array $data, CometClubCompetition $competition, array $logoMap): string
    {
        $uniqueData = [
            'matchFifaId' => $data['matchFifaId'] ?? null,
        ];

        $matchData = [
            'competitionFifaId' => $competition->competitionFifaId,
            'season' => $competition->season,
            'ageCategory' => $competition->ageCategory,
            'ageCategoryName' => $competition->ageCategoryName,
            'matchday' => $data['matchday'] ?? null,
            'homeTeamFifaId' => $data['homeTeamFifaId'] ?? null,
            'homeTeamName' => $data['homeTeamName'] ?? null,
            'homeTeamShortName' => $data['homeTeamShortName'] ?? null,
            'homeTeamLogo' => $logoMap[$data['homeTeamFifaId'] ?? ''] ?? null,
            'awayTeamFifaId' => $data['awayTeamFifaId'] ?? null,
            'awayTeamName' => $data['awayTeamName'] ?? null,
            'awayTeamShortName' => $data['awayTeamShortName'] ?? null,
            'awayTeamLogo' => $logoMap[$data['awayTeamFifaId'] ?? ''] ?? null,
            'scoreHome' => $data['scoreHome'] ?? null,
            'scoreAway' => $data['scoreAway'] ?? null,
            'matchDate' => $data['matchDate'] ?? null,
            'matchTime' => $data['matchTime'] ?? null,
            'status' => $data['status'] ?? 'scheduled',
            'venue' => $data['venue'] ?? null,
            'updated_at' => now(),
        ];

        $existing = CometMatch::where($uniqueData)->first();

        if (!$existing) {
            $matchData['created_at'] = now();
            CometMatch::create(array_merge($uniqueData, $matchData));
            return 'inserted';
        }

        // Check if data changed
        $changed = false;
        foreach ($matchData as $key => $value) {
            if ($existing->$key != $value) {
                $changed = true;
                break;
            }
        }

        if ($changed) {
            $existing->update($matchData);
            return 'updated';
        }

        return 'skipped';
    }

    /**
     * Upsert a ranking record
     */
    private function upsertRanking(array $data, CometClubCompetition $competition, array $logoMap): string
    {
        $uniqueData = [
            'competitionFifaId' => $competition->competitionFifaId,
            'teamFifaId' => $data['teamFifaId'] ?? null,
        ];

        $rankingData = [
            'season' => $competition->season,
            'ageCategory' => $competition->ageCategory,
            'ageCategoryName' => $competition->ageCategoryName,
            'teamName' => $data['teamName'] ?? null,
            'teamShortName' => $data['teamShortName'] ?? null,
            'teamLogo' => $logoMap[$data['teamFifaId'] ?? ''] ?? null,
            'rank' => $data['rank'] ?? null,
            'matchesPlayed' => $data['matchesPlayed'] ?? 0,
            'wins' => $data['wins'] ?? 0,
            'draws' => $data['draws'] ?? 0,
            'losses' => $data['losses'] ?? 0,
            'goalsFor' => $data['goalsFor'] ?? 0,
            'goalsAgainst' => $data['goalsAgainst'] ?? 0,
            'goalDifference' => $data['goalDifference'] ?? 0,
            'points' => $data['points'] ?? 0,
            'updated_at' => now(),
        ];

        $existing = CometRanking::where($uniqueData)->first();

        if (!$existing) {
            $rankingData['created_at'] = now();
            CometRanking::create(array_merge($uniqueData, $rankingData));
            return 'inserted';
        }

        $changed = false;
        foreach ($rankingData as $key => $value) {
            if ($existing->$key != $value) {
                $changed = true;
                break;
            }
        }

        if ($changed) {
            $existing->update($rankingData);
            return 'updated';
        }

        return 'skipped';
    }

    /**
     * Upsert a top scorer record
     */
    private function upsertTopScorer(array $data, CometClubCompetition $competition): string
    {
        $uniqueData = [
            'competitionFifaId' => $competition->competitionFifaId,
            'playerFifaId' => $data['playerFifaId'] ?? null,
        ];

        $scorerData = [
            'season' => $competition->season,
            'ageCategory' => $competition->ageCategory,
            'ageCategoryName' => $competition->ageCategoryName,
            'playerName' => $data['playerName'] ?? null,
            'teamFifaId' => $data['teamFifaId'] ?? null,
            'teamName' => $data['teamName'] ?? null,
            'goals' => $data['goals'] ?? 0,
            'assists' => $data['assists'] ?? 0,
            'matchesPlayed' => $data['matchesPlayed'] ?? 0,
            'updated_at' => now(),
        ];

        $existing = CometTopScorer::where($uniqueData)->first();

        if (!$existing) {
            $scorerData['created_at'] = now();
            CometTopScorer::create(array_merge($uniqueData, $scorerData));
            return 'inserted';
        }

        $changed = false;
        foreach ($scorerData as $key => $value) {
            if ($existing->$key != $value) {
                $changed = true;
                break;
            }
        }

        if ($changed) {
            $existing->update($scorerData);
            return 'updated';
        }

        return 'skipped';
    }

    /**
     * Normalize record for comparison (remove timestamps, sort keys)
     */
    public function normalizeRecord(array $row): array
    {
        // Remove common timestamp fields to avoid false differences
        $ignore = ['created_at', 'updated_at', 'last_synced_at', 'started_at', 'completed_at'];
        foreach ($ignore as $k) {
            if (array_key_exists($k, $row)) {
                unset($row[$k]);
            }
        }
        // Sort by keys for stable comparison
        ksort($row);
        return $row;
    }

    /**
     * Generic upsert with change detection
     * Returns: ['action' => 'inserted|updated|skipped', 'changed' => array_of_columns]
     */
    public function upsertIfChanged(string $table, array $keyCols, array $data, string $connection = null): array
    {
        $query = DB::connection($connection)->table($table);

        // Build where clause from key columns
        foreach ($keyCols as $col => $val) {
            $query->where($col, $val);
        }

        $existing = $query->first();

        if (!$existing) {
            // Insert new record
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::connection($connection)->table($table)->insert($data);

            return ['action' => 'inserted', 'changed' => array_keys($data)];
        }

        // Convert existing to array
        $existingArray = (array) $existing;

        // Determine changed columns
        $changed = [];
        foreach ($data as $col => $val) {
            $old = $existingArray[$col] ?? null;
            // Normalize for comparison
            $oldStr = $old === null ? null : (string)$old;
            $newStr = $val === null ? null : (string)$val;

            if ($oldStr !== $newStr) {
                $changed[] = $col;
            }
        }

        if (empty($changed)) {
            return ['action' => 'skipped', 'changed' => []];
        }

        // Update record
        $data['updated_at'] = now();

        $updateQuery = DB::connection($connection)->table($table);
        foreach ($keyCols as $col => $val) {
            $updateQuery->where($col, $val);
        }
        $updateQuery->update($data);

        return ['action' => 'updated', 'changed' => $changed];
    }
}
