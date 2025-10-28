<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncLog;

class SyncCometTopScorers extends Command
{
    protected $signature = 'comet:sync-topscorers';
    protected $description = 'Sync all top scorers from Comet API to landlord database';

    public function handle()
    {
        // Start sync log
        $syncLog = SyncLog::startSync('comet_top_scorers');

        try {
            $this->info('⏱️  SYNC COMET TOP SCORERS');
            $this->line(str_repeat('=', 60));

        require_once base_path('lib/sync_helpers.php');

        $mysqli = new \mysqli('localhost', 'root', '', 'kpkb3');
        if ($mysqli->connect_error) {
            $this->error("Connection failed: " . $mysqli->connect_error);
            return 1;
        }

        // Load team logos
        $this->info('🖼️  Loading team logos...');
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

        $statsInserted = $statsUpdated = $statsSkipped = $errors = $totalScorers = 0;

        $bar = $this->output->createProgressBar(count($competitions));
        $bar->start();

        foreach ($competitions as $comp) {
            $ch = curl_init("{$apiUrl}/competition/{$comp['competitionFifaId']}/topScorers");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode != 200) {
                $bar->advance();
                continue;
            }

            $scorers = json_decode($response, true);
            if (empty($scorers)) {
                $bar->advance();
                continue;
            }

            $totalScorers += count($scorers);

            foreach ($scorers as $scorer) {
                if (empty($scorer['personFifaId'])) continue;

                $keyCols = [
                    'competition_fifa_id' => $comp['competitionFifaId'],
                    'player_fifa_id' => $scorer['personFifaId']
                ];

                $fullName = $scorer['internationalName'] ?? '';
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                $clubId = $scorer['teamFifaId'] ?? null;
                $teamLogo = ($clubId && isset($logoMap[$clubId])) ? $logoMap[$clubId] : null;

                $data = [
                    'competition_fifa_id' => $comp['competitionFifaId'],
                    'international_competition_name' => $comp['internationalName'],
                    'age_category' => $comp['ageCategoryName'],
                    'age_category_name' => $comp['ageCategoryName'],
                    'player_fifa_id' => $scorer['personFifaId'],
                    'goals' => $scorer['goals'] ?? 0,
                    'international_first_name' => $firstName,
                    'international_last_name' => $lastName,
                    'club' => $scorer['teamInternationalName'] ?? null,
                    'club_id' => $scorer['teamFifaId'] ?? null,
                    'team_logo' => $teamLogo
                ];

                try {
                    $result = upsert_if_changed($mysqli, 'comet_top_scorers', $keyCols, $data);

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

        $this->info('✅ COMET TOP SCORERS SYNC COMPLETED');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Competitions processed', count($competitions)],
                ['Total top scorers', $totalScorers],
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
            'total' => $totalScorers,
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
