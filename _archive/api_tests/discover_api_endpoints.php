<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$auth = base64_encode("$username:$password");

$headers = [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/json'
];

function makeApiCall($url, $headers) {
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
    curl_close($ch);

    return ['status' => $httpCode, 'data' => $response];
}

echo "【 Testing Different API Endpoint Patterns 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$testUrls = [
    'Test 1: /api/export/comet/matches?teamFifaId=598' =>
        $baseUrl . '/api/export/comet/matches?teamFifaId=598',
    'Test 2: /api/export/comet/matches?competitionFifaId=100629221' =>
        $baseUrl . '/api/export/comet/matches?competitionFifaId=100629221',
    'Test 3: /api/export/comet/matches' =>
        $baseUrl . '/api/export/comet/matches',
    'Test 4: /api/export/comet/teams?teamFifaId=598' =>
        $baseUrl . '/api/export/comet/teams?teamFifaId=598',
    'Test 5: /api/export/comet/teams' =>
        $baseUrl . '/api/export/comet/teams',
    'Test 6: /api/export/comet/clubs?clubFifaId=598' =>
        $baseUrl . '/api/export/comet/clubs?clubFifaId=598',
    'Test 7: /api/export/comet/clubs' =>
        $baseUrl . '/api/export/comet/clubs',
];

foreach ($testUrls as $label => $url) {
    $result = makeApiCall($url, $headers);
    echo "$label\n";
    echo "  Status: " . $result['status'] . "\n";

    if ($result['status'] === 200) {
        $json = json_decode($result['data'], true);
        if ($json) {
            echo "  ✅ SUCCESS! Keys: " . implode(', ', array_keys($json)) . "\n";
            // Show first entry preview
            foreach ($json as $key => $val) {
                if (is_array($val) && !empty($val)) {
                    echo "  Sample $key (first): " . json_encode(array_slice($val, 0, 1)) . "\n";
                    break;
                }
            }
        } else {
            echo "  Invalid JSON\n";
        }
    }
    echo "\n";
}
