<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CometApiService;
use App\Models\Comet\CometRanking;
use App\Models\Comet\CometClubCompetition;
use Illuminate\Support\Facades\DB;

$api = new CometApiService();

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   COMET RANKINGS SYNC - ALL COMPETITIONS                     ║\n";
echo "║   NK Prigorje Competitions | Season: 2025/2026              ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Hole alle Competitions von NK Prigorje
$competitions = DB::connection('central')
    ->table('comet_club_competitions')
    ->get();

echo "Found " . $competitions->count() . " competitions for NK Prigorje\n\n";

$totalRankings = 0;
$syncedRankings = 0;

foreach ($competitions as $clubComp) {
    echo "📊 {$clubComp->internationalName}\n";
    echo str_repeat('-', 70) . "\n";

    try {
        // Hole Rankings von der API
        $rankings = $api->getCompetitionRanking($clubComp->competitionFifaId);

        $totalRankings += count($rankings);

        foreach ($rankings as $ranking) {
            try {
                // Speichere Ranking
                CometRanking::updateOrCreate(
                    [
                        'competition_fifa_id' => $clubComp->competitionFifaId,
                        'team_fifa_id' => $ranking['teamFifaId'] ?? null,
                    ],
                    [
                        'international_competition_name' => $clubComp->internationalName,
                        'age_category' => $clubComp->ageCategory,
                        'age_category_name' => $clubComp->ageCategoryName,
                        'position' => $ranking['position'] ?? null,
                        'team_image_logo' => $ranking['teamImageLogo'] ?? null,
                        'international_team_name' => $ranking['internationalName'] ?? 'Unknown',
                        'matches_played' => $ranking['matchesPlayed'] ?? 0,
                        'wins' => $ranking['wins'] ?? 0,
                        'draws' => $ranking['draws'] ?? 0,
                        'losses' => $ranking['losses'] ?? 0,
                        'goals_for' => $ranking['goalsFor'] ?? 0,
                        'goals_against' => $ranking['goalsAgainst'] ?? 0,
                        'goal_difference' => ($ranking['goalsFor'] ?? 0) - ($ranking['goalsAgainst'] ?? 0),
                        'points' => $ranking['points'] ?? 0,
                    ]
                );

                $syncedRankings++;

                // Highlight NK Prigorje (Team 598)
                $isNKPrigorje = ($ranking['teamFifaId'] ?? 0) == 598;
                $prefix = $isNKPrigorje ? '  ★' : '   ';

                $pos = str_pad($ranking['position'] ?? '?', 2, ' ', STR_PAD_LEFT);
                $team = str_pad(substr($ranking['internationalName'] ?? 'Unknown', 0, 25), 25);
                $played = str_pad($ranking['matchesPlayed'] ?? 0, 2, ' ', STR_PAD_LEFT);
                $w = str_pad($ranking['wins'] ?? 0, 2, ' ', STR_PAD_LEFT);
                $d = str_pad($ranking['draws'] ?? 0, 2, ' ', STR_PAD_LEFT);
                $l = str_pad($ranking['losses'] ?? 0, 2, ' ', STR_PAD_LEFT);
                $gf = str_pad($ranking['goalsFor'] ?? 0, 3, ' ', STR_PAD_LEFT);
                $ga = str_pad($ranking['goalsAgainst'] ?? 0, 3, ' ', STR_PAD_LEFT);
                $gd = ($ranking['goalsFor'] ?? 0) - ($ranking['goalsAgainst'] ?? 0);
                $gdStr = str_pad(($gd >= 0 ? '+' : '') . $gd, 4, ' ', STR_PAD_LEFT);
                $pts = str_pad($ranking['points'] ?? 0, 3, ' ', STR_PAD_LEFT);

                echo "{$prefix}{$pos}. {$team} | P:{$played} W:{$w} D:{$d} L:{$l} | GF:{$gf} GA:{$ga} GD:{$gdStr} | Pts:{$pts}\n";

            } catch (\Exception $e) {
                echo "  ⚠️  Error syncing ranking for team {$ranking['teamFifaId']}: " . $e->getMessage() . "\n";
            }
        }

        echo "\n";

    } catch (\Exception $e) {
        echo "  ❌ Error fetching rankings: " . $e->getMessage() . "\n\n";
    }
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                       SYNC SUMMARY                            ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Rankings (API)                                   " . str_pad($totalRankings, 6) . " ║\n";
echo "║ Synced Rankings (ALL)                                  " . str_pad($syncedRankings, 6) . " ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";

echo "\n✅ RANKINGS SYNC COMPLETED!\n";
echo "📊 Data is in comet_rankings table\n";
echo "★ = NK Prigorje (Team FIFA ID: 598)\n";
