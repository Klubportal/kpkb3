<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncLog;

class SyncCometRankings extends Command
{
    protected $signature = 'comet:sync-rankings';
    protected $description = 'Sync all rankings from Comet API to landlord database';

    public function handle()
    {
        // Start sync log
        $syncLog = SyncLog::startSync('comet_rankings');

        try {
            $this->info('⏱️  SYNC COMET RANKINGS');
            $this->line(str_repeat('=', 60));

        require_once base_path('lib/sync_helpers.php');

        $mysqli = new \mysqli('localhost', 'root', '', 'kpkb3');
        if ($mysqli->connect_error) {
            $this->error("Connection failed: " . $mysqli->connect_error);
            return 1;
        }

        // Load team logos
        $this->info('📷 Loading team logos...');
        $logoDir = public_path('images/kp_team_logo_images');
        $logoMap = [];

        if (is_dir($logoDir)) {
            $logoFiles = glob($logoDir . '/*');
            foreach ($logoFiles as $logoFile) {
                $filename = basename($logoFile);
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $teamId = pathinfo($filename, PATHINFO_FILENAME);

                if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) {
                    if (!isset($logoMap[$teamId]) || $extension === 'png') {
                        $logoMap[$teamId] = '/images/kp_team_logo_images/' . $filename;
                    }
                }
            }
        }

        $this->line("   Found " . count($logoMap) . " team logos");

        $apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
        $username = 'nkprigorje';
        $password = '3c6nR$dS';

        $competitions = $mysqli->query("
            SELECT competitionFifaId, internationalName, ageCategoryName
            FROM comet_club_competitions
            WHERE status = 'active'
        ")->fetch_all(MYSQLI_ASSOC);

        $this->info("\n📊 Active Competitions: " . count($competitions));

        $statsInserted = $statsUpdated = $statsSkipped = $errors = $totalRankings = 0;

        $bar = $this->output->createProgressBar(count($competitions));
        $bar->start();

        foreach ($competitions as $comp) {
            $ch = curl_init("{$apiUrl}/competition/{$comp['competitionFifaId']}/ranking");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode != 200 || empty($response)) {
                $bar->advance();
                continue;
            }

            $rankings = json_decode($response, true);
            if (empty($rankings)) {
                $bar->advance();
                continue;
            }

            $totalRankings += count($rankings);

            foreach ($rankings as $rank) {
                $keyCols = [
                    'competition_fifa_id' => $comp['competitionFifaId'],
                    'team_fifa_id' => $rank['teamFifaId']
                ];

                $teamFifaId = $rank['teamFifaId'];
                $teamLogo = isset($logoMap[$teamFifaId]) ? $logoMap[$teamFifaId] : null;
                $teamName = $rank['team']['internationalShortName'] ?? $rank['team']['internationalName'] ?? null;

                $data = [
                    'competition_fifa_id' => $comp['competitionFifaId'],
                    'team_fifa_id' => $rank['teamFifaId'],
                    'international_team_name' => $teamName,
                    'team_image_logo' => $teamLogo,
                    'position' => $rank['position'] ?? null,
                    'matches_played' => $rank['matchesPlayed'] ?? null,
                    'wins' => $rank['wins'] ?? null,
                    'draws' => $rank['draws'] ?? null,
                    'losses' => $rank['losses'] ?? null,
                    'goals_for' => $rank['goalsFor'] ?? null,
                    'goals_against' => $rank['goalsAgainst'] ?? null,
                    'goal_difference' => $rank['goalDifference'] ?? null,
                    'points' => $rank['points'] ?? null
                ];

                try {
                    $result = upsert_if_changed($mysqli, 'comet_rankings', $keyCols, $data);

                    if ($result['action'] === 'inserted') $statsInserted++;
                    elseif ($result['action'] === 'updated') $statsUpdated++;
                    else $statsSkipped++;
                } catch (\Exception $e) {
                    $errors++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ COMET RANKINGS SYNC COMPLETED');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Competitions processed', count($competitions)],
                ['Total rankings', $totalRankings],
                ['Inserted', $statsInserted],
                ['Updated', $statsUpdated],
                ['Skipped', $statsSkipped],
                ['Errors', $errors],
            ]
        );

        $mysqli->close();

        // Complete sync log
        $syncLog->complete([
            'inserted' => $statsInserted,
            'updated' => $statsUpdated,
            'skipped' => $statsSkipped,
            'failed' => $errors,
            'total' => $totalRankings,
            'status' => $errors > 0 ? 'partial' : 'success',
            'metadata' => [
                'competitions_processed' => count($competitions),
            ],
        ]);

        return 0;

        } catch (\Exception $e) {
            $syncLog->fail($e->getMessage(), $e->getTraceAsString());
            $this->error('Sync failed: ' . $e->getMessage());
            return 1;
        }
    }
}
