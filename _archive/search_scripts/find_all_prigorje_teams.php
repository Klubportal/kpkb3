<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SUCHE NACH ALLEN NK PRIGORJE TEAMS ===\n\n";

$api = new \App\Services\CometApiService();

// Search for all competitions with organisation 10 (Zagrebački NS)
echo "1. Hole ALLE Competitions von Organisation 10 (Zagrebački NS):\n";
echo str_repeat('-', 80) . "\n";

try {
    $allComps = $api->getCompetitions([
        'active' => true,
    ]);

    echo "Total competitions from API: " . count($allComps) . "\n\n";

    // Filter for season 2025/2026
    $filtered = array_filter($allComps, function($comp) {
        $season = $comp['season'] ?? '';
        return (str_contains($season, '2025') || str_contains($season, '2026'));
    });

    echo "Competitions for season 2025/2026: " . count($filtered) . "\n\n";

    // Now check each competition for NK Prigorje teams
    echo "2. Suche nach NK Prigorje in allen Competitions:\n";
    echo str_repeat('-', 80) . "\n";

    $prigorjeCompetitions = [];
    $teamFifaIds = [];

    foreach ($filtered as $comp) {
        try {
            $teams = $api->getCompetitionTeams($comp['competitionFifaId']);

            foreach ($teams as $team) {
                // Check if it's NK Prigorje
                if (str_contains(strtolower($team['internationalName']), 'prigorje')) {
                    $prigorjeCompetitions[] = [
                        'competition' => $comp['internationalName'],
                        'competitionId' => $comp['competitionFifaId'],
                        'season' => $comp['season'],
                        'teamName' => $team['internationalName'],
                        'teamFifaId' => $team['teamFifaId'],
                        'organisationFifaId' => $team['organisationFifaId'],
                    ];

                    $teamFifaIds[$team['teamFifaId']] = $team['internationalName'];
                }
            }
        } catch (\Exception $e) {
            // Skip competitions we can't access
        }
    }

    echo "\n3. GEFUNDEN - NK Prigorje Teams:\n";
    echo str_repeat('-', 80) . "\n";

    foreach ($teamFifaIds as $id => $name) {
        echo "Team FIFA ID: $id - $name\n";
    }

    echo "\n4. NK Prigorje Competitions (Total: " . count($prigorjeCompetitions) . "):\n";
    echo str_repeat('-', 80) . "\n";

    foreach ($prigorjeCompetitions as $item) {
        echo "✓ {$item['competition']}\n";
        echo "  Competition ID: {$item['competitionId']}\n";
        echo "  Team: {$item['teamName']} (FIFA ID: {$item['teamFifaId']})\n";
        echo "  Organisation: {$item['organisationFifaId']}\n";
        echo "  Season: {$item['season']}\n\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
