<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CometApiService;
use App\Models\Comet\CometTopScorer;
use Illuminate\Support\Facades\DB;

$api = new CometApiService();

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   COMET TOP SCORERS SYNC - ALL COMPETITIONS                  ║\n";
echo "║   Season: 2025/2026                                          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Hole ALLE Competitions aus der Datenbank
$competitions = DB::connection('central')
    ->table('comet_competitions')
    ->get();

echo "Found " . $competitions->count() . " total competitions\n\n";

$totalScorers = 0;
$syncedScorers = 0;
$skippedCompetitions = 0;

foreach ($competitions as $comp) {
    echo "⚽ {$comp->name} (ID: {$comp->comet_id})\n";
    echo str_repeat('-', 70) . "\n";

    try {
        // Hole Top Scorers von der API
        $scorers = $api->getCompetitionTopScorers($comp->comet_id);

        if (empty($scorers)) {
            echo "  ⚠️  No top scorers available\n\n";
            $skippedCompetitions++;
            continue;
        }

        $totalScorers += count($scorers);

        foreach ($scorers as $scorer) {
            try {
                // Speichere Top Scorer
                CometTopScorer::updateOrCreate(
                    [
                        'competition_fifa_id' => $comp->comet_id,
                        'player_fifa_id' => $scorer['personFifaId'] ?? null,
                    ],
                    [
                        'international_competition_name' => $comp->name,
                        'age_category' => $comp->age_category ?? 'UNKNOWN',
                        'age_category_name' => $comp->age_category ?? 'Unknown',
                        'goals' => $scorer['goals'] ?? 0,
                        'international_first_name' => $scorer['internationalFirstName'] ?? '',
                        'international_last_name' => $scorer['internationalLastName'] ?? '',
                        'club' => $scorer['teamInternationalName'] ?? null,
                        'club_id' => $scorer['teamFifaId'] ?? 0,
                        'team_logo' => $scorer['teamImageLogo'] ?? null,
                    ]
                );

                $syncedScorers++;

            } catch (\Exception $e) {
                echo "  ⚠️  Error syncing scorer: " . $e->getMessage() . "\n";
            }
        }

        echo "  ✓ Synced " . count($scorers) . " scorers\n\n";

    } catch (\Exception $e) {
        echo "  ❌ Error fetching top scorers: " . $e->getMessage() . "\n\n";
        $skippedCompetitions++;
    }
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                       SYNC SUMMARY                            ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Competitions                                     " . str_pad($competitions->count(), 6) . " ║\n";
echo "║ Competitions with Scorers                              " . str_pad($competitions->count() - $skippedCompetitions, 6) . " ║\n";
echo "║ Total Top Scorers (API)                                " . str_pad($totalScorers, 6) . " ║\n";
echo "║ Synced Top Scorers                                     " . str_pad($syncedScorers, 6) . " ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";

echo "\n✅ ALL COMPETITIONS TOP SCORERS SYNC COMPLETED!\n";
echo "📊 Data is in comet_top_scorers table\n";
