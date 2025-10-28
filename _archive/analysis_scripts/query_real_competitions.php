<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;
use App\Models\CometCompetition;
use Illuminate\Support\Facades\DB;

const CLUB_FIFA_ID = 598;

echo "【 Fetch ALL Active Competitions for Club 598 】\n";
echo "   Season: 2025/2026 (25 or 26)\n";
echo "════════════════════════════════════════════════════════════════\n";

// Comet API credentials
$apiUrl = 'https://api-hns.analyticom.de';
$apiUsername = 'nkprigorje';
$apiPassword = '3c6nR$dS';

$client = new Client([
    'auth' => [$apiUsername, $apiPassword],
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => false,
]);

echo "\n【 Step 1: Try Club Info Endpoint 】\n";

// Try to get club info which might list competitions
try {
    echo "📡 GET /club/598\n";
    $response = $client->request('GET', "{$apiUrl}/club/598");
    $clubData = json_decode($response->getBody(), true);

    echo "✅ Club info retrieved:\n";
    print_r($clubData);

} catch (\Exception $e) {
    echo "⚠️  Endpoint not available\n";
}

echo "\n【 Step 2: Query Matches to find Competitions 】\n";

try {
    echo "📡 GET /matches?club=598\n";
    $response = $client->request('GET', "{$apiUrl}/matches", [
        'query' => [
            'club' => 598,
            'limit' => 200,
        ]
    ]);

    $matchesData = json_decode($response->getBody(), true);

    if (isset($matchesData['data'])) {
        $matches = $matchesData['data'];
    } else {
        $matches = is_array($matchesData) ? $matchesData : [$matchesData];
    }

    echo "✅ Retrieved " . count($matches) . " matches\n";

    // Extract unique competitions from matches
    $competitions = [];
    foreach ($matches as $match) {
        if (isset($match['competition'])) {
            $comp = $match['competition'];
            if (!isset($competitions[$comp['fifaId'] ?? $comp['id'] ?? 0])) {
                $competitions[$comp['fifaId'] ?? $comp['id'] ?? 0] = $comp;
            }
        }
    }

    echo "✅ Found " . count($competitions) . " unique competitions\n\n";

    // Display competitions
    echo "【 Competitions Found 】\n";
    $counter = 0;
    foreach ($competitions as $compId => $comp) {
        $counter++;
        $compName = $comp['name'] ?? 'Unknown';
        $compId = $comp['fifaId'] ?? $comp['id'] ?? $compId;
        $compType = $comp['type'] ?? 'Unknown';
        $compSeason = $comp['season'] ?? 'Unknown';
        $compActive = isset($comp['active']) ? ($comp['active'] ? 'YES' : 'NO') : 'Unknown';

        echo "\n{$counter}. {$compName}\n";
        echo "   ID: {$compId}\n";
        echo "   Type: {$compType}\n";
        echo "   Season: {$compSeason}\n";
        echo "   Active: {$compActive}\n";
    }

} catch (\Exception $e) {
    echo "⚠️  Error: {$e->getMessage()}\n";
}

echo "\n【 Step 3: List Competitions Endpoint 】\n";

try {
    echo "📡 GET /competitions\n";
    $response = $client->request('GET', "{$apiUrl}/competitions", [
        'query' => [
            'limit' => 200,
        ]
    ]);

    $compsData = json_decode($response->getBody(), true);

    if (isset($compsData['data'])) {
        $allComps = $compsData['data'];
    } else {
        $allComps = is_array($compsData) ? $compsData : [$compsData];
    }

    echo "✅ Retrieved " . count($allComps) . " competitions\n";

    // Filter for active and season 25/26
    $filtered = [];
    foreach ($allComps as $comp) {
        $season = $comp['season'] ?? 0;
        $active = $comp['active'] ?? $comp['status'] === 'ACTIVE' ?? false;

        // Check if season is 2025 or 2026 or "2025/2026"
        if ($active && (
            $season == 2025 ||
            $season == 2026 ||
            strpos($season, '2025') !== false ||
            strpos($season, '2026') !== false
        )) {
            $filtered[$comp['fifaId'] ?? $comp['id']] = $comp;
        }
    }

    echo "\n【 Active Competitions Season 25/26 】\n";
    echo "✅ Total: " . count($filtered) . "\n\n";

    $counter = 0;
    foreach ($filtered as $compId => $comp) {
        $counter++;
        $compName = $comp['name'] ?? 'Unknown';
        echo "{$counter}. {$compName}\n";
    }

} catch (\Exception $e) {
    echo "⚠️  Error: {$e->getMessage()}\n";
}

echo "\n════════════════════════════════════════════════════════════════\n";
