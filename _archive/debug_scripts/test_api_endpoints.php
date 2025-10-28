<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;

$baseUrl = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$auth = base64_encode("$username:$password");

$headers = [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/json'
];

function makeApiCall($url, $headers) {
    echo "  Calling: $url\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "  Status: $httpCode\n";
    if ($error) {
        echo "  Error: $error\n";
    }

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo "  Response: " . substr(json_encode($data), 0, 200) . "...\n";
        return $data;
    }

    echo "  Response: " . substr($response, 0, 200) . "\n";
    return null;
}

echo "【 Testing API Endpoints 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Get competitions
$competitions = CometCompetition::where('status', 'ACTIVE')
    ->where('season', 2026)
    ->limit(2)
    ->get();

echo "Testing with 2 competitions:\n\n";

foreach ($competitions as $comp) {
    echo "Competition: {$comp->international_name} (FIFA ID: {$comp->competition_fifa_id})\n";
    echo "──────────────────────────────────────────────────────────────\n";

    // Test teams endpoint
    echo "1. Teams Endpoint:\n";
    $url = $baseUrl . '/api/export/comet/teams?competitionFifaId=' . $comp->competition_fifa_id;
    $data = makeApiCall($url, $headers);

    if ($data) {
        echo "  Keys: " . implode(', ', array_keys($data)) . "\n";
    }

    echo "\n2. Matches Endpoint:\n";
    $url = $baseUrl . '/api/export/comet/competitions/' . $comp->competition_fifa_id . '/matches';
    $data = makeApiCall($url, $headers);

    if ($data) {
        echo "  Keys: " . implode(', ', array_keys($data)) . "\n";
    }

    echo "\n";
}
