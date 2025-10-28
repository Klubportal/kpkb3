<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CometApiService;
use App\Models\Comet\CometMatchSimple;
use App\Models\Comet\CometClubCompetition;
use Illuminate\Support\Facades\DB;

$api = new CometApiService();

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   COMET MATCHES SYNC - ALL MATCHES                           ║\n";
echo "║   NK Prigorje Competitions | Season: 2025/2026              ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Hole alle Competitions von NK Prigorje
$competitions = DB::connection('central')
    ->table('comet_club_competitions')
    ->get();

echo "Found " . $competitions->count() . " competitions for NK Prigorje\n\n";

$totalMatches = 0;
$syncedMatches = 0;

foreach ($competitions as $clubComp) {
    echo "📋 {$clubComp->internationalName}\n";
    echo str_repeat('-', 70) . "\n";

    try {
        // Hole Matches von der API
        $matches = $api->getCompetitionMatches($clubComp->competitionFifaId);

        $totalMatches += count($matches);

        foreach ($matches as $match) {
            try {
                // Extrahiere Home/Away Teams aus matchTeams Array
                $homeTeamId = null;
                $awayTeamId = null;
                $homeTeamName = null;
                $awayTeamName = null;

                // matchTeams durchsuchen
                if (isset($match['matchTeams']) && is_array($match['matchTeams'])) {
                    foreach ($match['matchTeams'] as $team) {
                        if ($team['isHome'] ?? false) {
                            $homeTeamId = $team['teamFifaId'] ?? null;
                            $homeTeamName = $team['internationalName'] ?? null;
                        } else {
                            $awayTeamId = $team['teamFifaId'] ?? null;
                            $awayTeamName = $team['internationalName'] ?? null;
                        }
                    }
                }

                // KEIN FILTER - Alle Matches synchronisieren

                // Speichere Match
                CometMatchSimple::updateOrCreate(
                    ['match_fifa_id' => $match['matchFifaId']],
                    [
                        'competition_fifa_id' => $clubComp->competitionFifaId,
                        'age_category' => $clubComp->ageCategory,
                        'age_category_name' => $clubComp->ageCategoryName,
                        'international_competition_name' => $clubComp->internationalName,
                        'season' => $clubComp->season,
                        'competition_status' => $clubComp->status,
                        'match_status' => strtolower($match['status'] ?? 'unknown'),
                        'match_day' => $match['matchDay'] ?? null,
                        'match_place' => null, // Wird später gefüllt
                        'date_time_local' => isset($match['dateTimeLocal']) ? date('Y-m-d H:i:s', strtotime($match['dateTimeLocal'])) : null,
                        'team_fifa_id_home' => $homeTeamId,
                        'team_name_home' => $homeTeamName,
                        'team_score_home' => $match['homeFinalResult'] ?? null,
                        'team_logo_home' => null,
                        'team_fifa_id_away' => $awayTeamId,
                        'team_name_away' => $awayTeamName,
                        'team_score_away' => $match['awayFinalResult'] ?? null,
                        'team_logo_away' => null,
                    ]
                );

                $syncedMatches++;

                // Status anzeigen
                $status = $match['status'] ?? 'unknown';
                $homeScore = $match['homeFinalResult'] ?? '?';
                $awayScore = $match['awayFinalResult'] ?? '?';

                echo "  ✓ {$homeTeamName} {$homeScore}:{$awayScore} {$awayTeamName} ({$status})\n";

            } catch (\Exception $e) {
                echo "  ⚠️  Error syncing match {$match['matchFifaId']}: " . $e->getMessage() . "\n";
            }
        }        echo "\n";

    } catch (\Exception $e) {
        echo "  ❌ Error fetching matches: " . $e->getMessage() . "\n\n";
    }
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                       SYNC SUMMARY                            ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Matches (API)                                    " . str_pad($totalMatches, 6) . " ║\n";
echo "║ Synced Matches (ALL)                                   " . str_pad($syncedMatches, 6) . " ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";

echo "\n✅ MATCHES SYNC COMPLETED!\n";
echo "📊 Data is in comet_matches_simple table\n";
