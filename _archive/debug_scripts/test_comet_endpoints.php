<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;

echo "【 Comet API HNS (Croatian) - Endpoint Discovery 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$apiUrl = 'https://api-hns.analyticom.de';
$apiUsername = 'nkprigorje';
$apiPassword = '3c6nR$dS';

echo "API URL: {$apiUrl}\n";
echo "Username: {$apiUsername}\n";
echo "Password: ••••••••\n\n";

$client = new Client([
    'auth' => [$apiUsername, $apiPassword],
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => false,
]);

// Test various endpoint patterns with correct /api/export/comet/ path
$endpoints = [
    'GET /api/export/comet/throttling/info' => '/api/export/comet/throttling/info',
    'GET /api/export/comet/clubs' => '/api/export/comet/clubs',
    'GET /api/export/comet/club/598' => '/api/export/comet/club/598',
    'GET /api/export/comet/competitions?teamFifaId=598' => '/api/export/comet/competitions?teamFifaId=598',
    'GET /api/export/comet/competitions?active=true&teamFifaId=598' => '/api/export/comet/competitions?active=true&teamFifaId=598',
    'GET /api/export/comet/matches' => '/api/export/comet/matches?club=598&limit=5',
];

foreach ($endpoints as $label => $endpoint) {
    echo "【 {$label} 】\n";
    try {
        $response = $client->request('GET', "{$apiUrl}{$endpoint}", [
            'http_errors' => false
        ]);

        $status = $response->getStatusCode();
        $body = (string)$response->getBody();

        echo "Status: {$status}\n";

        if ($status === 200) {
            $data = json_decode($body, true);
            if (is_array($data)) {
                echo "✅ Response: JSON Array (" . count($data) . " items)\n";
                if (count($data) > 0) {
                    echo "First item keys: " . implode(', ', array_keys((array)$data[0])) . "\n";
                }
            } else if (is_object(json_decode($body))) {
                echo "✅ Response: JSON Object\n";
                $keys = array_keys((array)json_decode($body, true));
                echo "Keys: " . implode(', ', array_slice($keys, 0, 5)) . "\n";
            } else {
                echo "✅ Response: " . substr($body, 0, 100) . "...\n";
            }
        } else {
            echo "⚠️  Status {$status}\n";
            echo "Response: " . substr($body, 0, 200) . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "════════════════════════════════════════════════════════════════\n";
echo "✅ Endpoint Discovery Complete\n";
