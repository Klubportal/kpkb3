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

function testEndpoint($name, $url, $headers) {
    echo "\n$name\n";
    echo str_repeat('─', 70) . "\n";
    echo "URL: $url\n";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_VERBOSE => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Status: $httpCode";

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo " ✅\n";
            if (isset($data['data'])) {
                echo "Records: " . count($data['data']) . "\n";
            }
            if (isset($data['competitions'])) {
                echo "Competitions: " . count($data['competitions']) . "\n";
            }
            if (isset($data['teams'])) {
                echo "Teams: " . count($data['teams']) . "\n";
            }
            if (isset($data['matches'])) {
                echo "Matches: " . count($data['matches']) . "\n";
            }
            echo "Top-level keys: " . implode(', ', array_keys($data)) . "\n";
        }
    } else {
        echo "\n";
    }
}

echo "【 Testing /api/export/comet/ Endpoints 】\n";
echo "════════════════════════════════════════════════════════════════════\n";

$comp = CometCompetition::where('status', 'ACTIVE')->where('season', 2026)->first();
$compId = $comp ? $comp->competition_fifa_id : 100629221;

echo "\nUsing Competition FIFA ID: $compId\n";

testEndpoint(
    'Test 1: /api/export/comet/competitions',
    $baseUrl . '/api/export/comet/competitions',
    $headers
);

testEndpoint(
    'Test 2: /api/export/comet/competitions with active=true',
    $baseUrl . '/api/export/comet/competitions?active=true',
    $headers
);

testEndpoint(
    'Test 3: /api/export/comet/clubs',
    $baseUrl . '/api/export/comet/clubs',
    $headers
);

testEndpoint(
    'Test 4: /api/export/comet/teams',
    $baseUrl . '/api/export/comet/teams',
    $headers
);

testEndpoint(
    'Test 5: /api/export/comet/teams with competitionFifaId',
    $baseUrl . '/api/export/comet/teams?competitionFifaId=' . $compId,
    $headers
);

testEndpoint(
    'Test 6: /api/export/comet/matches',
    $baseUrl . '/api/export/comet/matches',
    $headers
);

testEndpoint(
    'Test 7: /api/export/comet/matches with competitionFifaId',
    $baseUrl . '/api/export/comet/matches?competitionFifaId=' . $compId,
    $headers
);

testEndpoint(
    'Test 8: /api/export/comet/players',
    $baseUrl . '/api/export/comet/players',
    $headers
);

testEndpoint(
    'Test 9: /api/export/comet/players with competitionFifaId',
    $baseUrl . '/api/export/comet/players?competitionFifaId=' . $compId,
    $headers
);

echo "\n" . str_repeat('═', 70) . "\n";
