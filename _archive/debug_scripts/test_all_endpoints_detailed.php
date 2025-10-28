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
    $error = curl_error($ch);
    curl_close($ch);

    echo "Status: $httpCode\n";

    if ($error) {
        echo "Error: $error\n";
        return;
    }

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo "✅ SUCCESS\n";
            if (isset($data['data'])) {
                echo "Records: " . count($data['data']) . "\n";
            }
            if (isset($data['pagination'])) {
                echo "Pagination: " . json_encode($data['pagination']) . "\n";
            }
            echo "Response Keys: " . implode(', ', array_keys($data)) . "\n";
        } else {
            echo "Invalid JSON response\n";
        }
    } else {
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
}

echo "【 Testing All Endpoint Variations 】\n";
echo "════════════════════════════════════════════════════════════════════\n";

// Get one competition FIFA ID to test with
$comp = CometCompetition::where('status', 'ACTIVE')->where('season', 2026)->first();
$compId = $comp ? $comp->competition_fifa_id : 100629221;

echo "\nUsing Competition FIFA ID: $compId\n";

testEndpoint(
    'Test 1: /clubs endpoint',
    $baseUrl . '/clubs',
    $headers
);

testEndpoint(
    'Test 2: /clubs with competition param',
    $baseUrl . '/clubs?competition=' . $compId,
    $headers
);

testEndpoint(
    'Test 3: /clubs with limit',
    $baseUrl . '/clubs?competition=' . $compId . '&limit=10',
    $headers
);

testEndpoint(
    'Test 4: /players endpoint',
    $baseUrl . '/players',
    $headers
);

testEndpoint(
    'Test 5: /players with competition',
    $baseUrl . '/players?competition=' . $compId,
    $headers
);

testEndpoint(
    'Test 6: /matches endpoint',
    $baseUrl . '/matches',
    $headers
);

testEndpoint(
    'Test 7: /matches with competition',
    $baseUrl . '/matches?competition=' . $compId,
    $headers
);

testEndpoint(
    'Test 8: /competitions endpoint',
    $baseUrl . '/competitions',
    $headers
);

echo "\n" . str_repeat('═', 70) . "\n";
