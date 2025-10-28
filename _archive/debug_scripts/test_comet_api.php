<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CometApiService;

$api = new CometApiService();

echo "=== TESTING COMET API ===\n\n";

// Test 1: Get all competitions
echo "1. Getting ALL competitions...\n";
try {
    $allComps = $api->getCompetitions();
    echo "✅ Total competitions: " . count($allComps) . "\n\n";

    foreach ($allComps as $comp) {
        $orgId = $comp['organisationFifaId'] ?? 'N/A';
        $season = $comp['season'] ?? 'N/A';
        $status = $comp['status'] ?? 'N/A';
        echo "  - {$comp['internationalName']} (Org: {$orgId}, Season: {$season}, Status: {$status})\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Get competitions for org 598
echo "2. Getting competitions for organisationFifaId=598...\n";
try {
    $filtered = $api->getCompetitions(['organisationFifaIds' => 598]);
    echo "✅ Filtered competitions: " . count($filtered) . "\n\n";

    foreach ($filtered as $comp) {
        $season = $comp['season'] ?? 'N/A';
        $status = $comp['status'] ?? 'N/A';
        echo "  - {$comp['internationalName']} (Season: {$season}, Status: {$status})\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Get team 618 players
echo "3. Getting players for team 618...\n";
try {
    $players = $api->getTeamPlayers(618, 'ALL');
    echo "✅ Total players: " . count($players) . "\n\n";

    if (!empty($players)) {
        echo "First 5 players:\n";
        foreach (array_slice($players, 0, 5) as $player) {
            $name = ($player['internationalFirstName'] ?? '') . ' ' . ($player['internationalLastName'] ?? '');
            $pos = $player['playerPosition'] ?? 'N/A';
            $status = $player['status'] ?? 'N/A';
            echo "  - {$name} ({$pos}, {$status})\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
