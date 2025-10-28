<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;

echo "【 COMET API - 11 Aktive Wettbewerbe für Club 598 (HNS) 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$apiUrl = 'https://api-hns.analyticom.de';
$apiUsername = 'nkprigorje';
$apiPassword = '3c6nR$dS';

$client = new Client([
    'auth' => [$apiUsername, $apiPassword],
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => false,
]);

try {
    echo "📡 Fetching: GET /api/export/comet/competitions?active=true&teamFifaId=598\n\n";

    $response = $client->request('GET', "{$apiUrl}/api/export/comet/competitions", [
        'query' => [
            'active' => 'true',
            'teamFifaId' => 598
        ]
    ]);

    $competitions = json_decode($response->getBody(), true);

    echo "✅ Found " . count($competitions) . " active competitions\n\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    $counter = 0;
    foreach ($competitions as $comp) {
        $counter++;
        $fifaId = $comp['competitionFifaId'] ?? 'N/A';
        $name = $comp['internationalName'] ?? 'Unknown';
        $shortName = $comp['internationalShortName'] ?? '';
        $season = $comp['season'] ?? 'N/A';
        $ageCategory = $comp['ageCategory'] ?? 'Unknown';
        $gender = $comp['gender'] ?? 'MALE';
        $status = $comp['status'] ?? 'ACTIVE';
        $nature = $comp['nature'] ?? 'LEAGUE';
        $dateFrom = $comp['dateFrom'] ?? 'N/A';
        $dateTo = $comp['dateTo'] ?? 'N/A';
        $teamChar = $comp['teamCharacter'] ?? 'CLUB';
        $matchType = $comp['matchType'] ?? 'OFFICIAL';

        echo "{$counter}. {$name}\n";
        echo "   FIFA ID: {$fifaId}\n";
        echo "   Short Name: {$shortName}\n";
        echo "   Season: {$season}\n";
        echo "   Age Category: {$ageCategory}\n";
        echo "   Gender: {$gender}\n";
        echo "   Nature: {$nature}\n";
        echo "   Match Type: {$matchType}\n";
        echo "   Team Character: {$teamChar}\n";
        echo "   Status: {$status}\n";
        echo "   Dates: {$dateFrom} to {$dateTo}\n";
        echo "\n";
    }

    echo "════════════════════════════════════════════════════════════════\n";
    echo "✅ Total: " . count($competitions) . " active competitions for Club 598, Season 25/26\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
