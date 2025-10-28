<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CometApiService;
use App\Models\Comet\CometTopScorer;
use App\Models\Comet\CometClubCompetition;
use Illuminate\Support\Facades\DB;

$api = new CometApiService();

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   COMET TOP SCORERS SYNC - NK PRIGORJE COMPETITIONS         ║\n";
echo "║   Team FIFA ID: 598 | Season: 2025/2026                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Hole alle Competitions von NK Prigorje
$competitions = DB::connection('central')
    ->table('comet_club_competitions')
    ->get();

echo "Found " . $competitions->count() . " competitions for NK Prigorje\n\n";

$totalScorers = 0;
$syncedScorers = 0;

foreach ($competitions as $clubComp) {
    echo "⚽ {$clubComp->internationalName}\n";
    echo str_repeat('-', 70) . "\n";

    try {
        // Hole Top Scorers von der API
        $scorers = $api->getCompetitionTopScorers($clubComp->competitionFifaId);

        if (empty($scorers)) {
            echo "  ⚠️  No top scorers available\n\n";
            continue;
        }

        $totalScorers += count($scorers);

        foreach ($scorers as $scorer) {
            try {
                // Speichere Top Scorer
                CometTopScorer::updateOrCreate(
                    [
                        'competition_fifa_id' => $clubComp->competitionFifaId,
                        'player_fifa_id' => $scorer['playerFifaId'] ?? null,
                    ],
                    [
                        'international_competition_name' => $clubComp->internationalName,
                        'age_category' => $clubComp->ageCategory,
                        'age_category_name' => $clubComp->ageCategoryName,
                        'goals' => $scorer['goals'] ?? 0,
                        'international_first_name' => $scorer['internationalFirstName'] ?? '',
                        'international_last_name' => $scorer['internationalLastName'] ?? '',
                        'club' => $scorer['club'] ?? null,
                        'club_id' => $scorer['clubId'] ?? 0,
                        'team_logo' => null, // wird später über update script geholt
                    ]
                );

                $syncedScorers++;

                // Highlight NK Prigorje players
                $isNKP = ($scorer['clubId'] ?? 0) == 598;
                $marker = $isNKP ? '🔵' : '  ';

                $firstName = $scorer['internationalFirstName'] ?? '';
                $lastName = $scorer['internationalLastName'] ?? '';
                $fullName = trim("$firstName $lastName");
                $goals = str_pad($scorer['goals'] ?? 0, 2, ' ', STR_PAD_LEFT);
                $club = $scorer['club'] ?? 'Unknown';

                echo sprintf(
                    "%s %2d ⚽  %-30s  (%s)\n",
                    $marker,
                    $scorer['goals'] ?? 0,
                    substr($fullName, 0, 30),
                    substr($club, 0, 30)
                );

            } catch (\Exception $e) {
                echo "  ⚠️  Error syncing scorer: " . $e->getMessage() . "\n";
            }
        }

        echo "\n";

    } catch (\Exception $e) {
        echo "  ❌ Error fetching top scorers: " . $e->getMessage() . "\n\n";
    }
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                       SYNC SUMMARY                            ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Top Scorers (API)                                " . str_pad($totalScorers, 6) . " ║\n";
echo "║ Synced Top Scorers                                     " . str_pad($syncedScorers, 6) . " ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";

echo "\n✅ TOP SCORERS SYNC COMPLETED!\n";
echo "📊 Data is in comet_top_scorers table\n";
echo "🔵 = NK Prigorje players\n";
