<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CometApiService;

$api = new CometApiService();

echo "=== SEARCHING FOR NK PRIGORJE ===\n\n";

// Get team 618 details
echo "1. Getting Team 618 Details...\n";
$players = $api->getTeamPlayers(618, 'ACTIVE');
echo "   Team 618 has " . count($players) . " players\n\n";

// Search through some competitions to find organisationFifaId
echo "2. Checking competitions to find which have NK Prigorje (team 618)...\n";
$competitions = $api->getCompetitions([]);
$nkPrigorjeCompetitions = [];

foreach ($competitions as $comp) {
    // Check if this competition has teams
    try {
        $teams = $api->getCompetitionTeams($comp['competitionFifaId']);

        foreach ($teams as $team) {
            if (isset($team['teamFifaId']) && $team['teamFifaId'] == 618) {
                $nkPrigorjeCompetitions[] = [
                    'competition' => $comp['internationalName'],
                    'competitionFifaId' => $comp['competitionFifaId'],
                    'organisationFifaId' => $comp['organisationFifaId'] ?? 'N/A',
                    'season' => $comp['season'] ?? 'N/A',
                    'status' => $comp['status'] ?? 'N/A',
                ];

                echo "   FOUND: {$comp['internationalName']}\n";
                echo "          Competition FIFA ID: {$comp['competitionFifaId']}\n";
                echo "          Organisation FIFA ID: " . ($comp['organisationFifaId'] ?? 'N/A') . "\n";
                echo "          Season: " . ($comp['season'] ?? 'N/A') . "\n";
                echo "          Status: " . ($comp['status'] ?? 'N/A') . "\n\n";

                // Stop after finding 5 competitions
                if (count($nkPrigorjeCompetitions) >= 5) {
                    break 2;
                }
            }
        }
    } catch (\Exception $e) {
        // Skip competitions that error
        continue;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Found " . count($nkPrigorjeCompetitions) . " competitions with NK Prigorje (Team 618)\n";
if (count($nkPrigorjeCompetitions) > 0) {
    echo "\nOrganisation FIFA IDs found:\n";
    $orgIds = array_unique(array_column($nkPrigorjeCompetitions, 'organisationFifaId'));
    foreach ($orgIds as $orgId) {
        echo "  - $orgId\n";
    }
}
