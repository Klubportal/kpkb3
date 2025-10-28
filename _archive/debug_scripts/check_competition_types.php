<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;

echo "【 COMET API - Competition Types Analysis 】\n";
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
    $response = $client->request('GET', "{$apiUrl}/api/export/comet/competitions", [
        'query' => [
            'active' => 'true',
            'teamFifaId' => 598
        ]
    ]);

    $competitions = json_decode($response->getBody(), true);

    echo "Competition Type Fields from API:\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    foreach ($competitions as $idx => $comp) {
        $name = $comp['internationalName'] ?? 'Unknown';
        $nature = $comp['nature'] ?? 'N/A';

        echo ($idx + 1) . ". {$name}\n";
        echo "   nature: {$nature}\n";

        // Check all available fields
        echo "   Available fields:\n";
        foreach ($comp as $key => $value) {
            if (is_string($value) && strlen($value) < 100) {
                echo "     - {$key}: {$value}\n";
            }
        }
        echo "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
