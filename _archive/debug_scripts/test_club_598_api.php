<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CometApiService;

$api = new CometApiService();

echo "=== TESTE API PARAMETER FÜR CLUB FIFA ID 598 ===\n\n";

// Teste verschiedene Parameter
$testParams = [
    ['clubFifaId' => 598],
    ['organisationFifaId' => 598],
    ['teamFifaId' => 598],
];

foreach ($testParams as $params) {
    $paramName = array_key_first($params);
    $paramValue = $params[$paramName];

    echo "Test mit Parameter: {$paramName} = {$paramValue}\n";
    echo str_repeat('-', 70) . "\n";

    try {
        $competitions = $api->getCompetitions(array_merge($params, ['active' => true]));

        // Filter für Season 2026
        $filtered = array_filter($competitions, function($comp) {
            $season = $comp['season'] ?? '';
            return str_contains($season, '2026');
        });

        echo "Gefundene Competitions (Season 2026): " . count($filtered) . "\n";

        if (count($filtered) > 0 && count($filtered) <= 15) {
            foreach ($filtered as $comp) {
                echo "  - {$comp['internationalName']}\n";
            }
        }

    } catch (\Exception $e) {
        echo "  FEHLER: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

// Jetzt teste ich: Hole ALLE Competitions und filtere die, wo ein Team von Club 598 teilnimmt
echo "\n=== ALTERNATIVE: Durchsuche ALLE Competitions nach Club 598 ===\n";
echo str_repeat('=', 70) . "\n\n";

try {
    $allCompetitions = $api->getCompetitions(['active' => true]);

    echo "Gesamt Competitions von API: " . count($allCompetitions) . "\n";

    // Filter für Season 2026
    $season2026 = array_filter($allCompetitions, function($comp) {
        $season = $comp['season'] ?? '';
        return str_contains($season, '2026');
    });

    echo "Season 2026 Competitions: " . count($season2026) . "\n\n";

    echo "Suche in allen Competitions nach Teams von Club 598...\n\n";

    $club598Competitions = [];
    $checkedCount = 0;

    foreach ($season2026 as $comp) {
        if ($checkedCount >= 50) { // Limit für Test
            echo "  (Stoppe nach 50 Checks...)\n";
            break;
        }

        try {
            $teams = $api->getCompetitionTeams($comp['competitionFifaId']);

            foreach ($teams as $team) {
                if (($team['organisationFifaId'] ?? null) == 598) {
                    $club598Competitions[] = $comp['internationalName'];
                    echo "  ✓ Gefunden: {$comp['internationalName']}\n";
                    break;
                }
            }

            $checkedCount++;
            usleep(100000); // 100ms delay

        } catch (\Exception $e) {
            // Skip
        }
    }

    echo "\nGesamt gefunden: " . count($club598Competitions) . " Competitions\n";

} catch (\Exception $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
}
