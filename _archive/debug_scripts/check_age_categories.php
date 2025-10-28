<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;

echo "【 COMET API - Age Categories Analysis 】\n";
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

    echo "Age Category Mappings from API:\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    foreach ($competitions as $idx => $comp) {
        $name = $comp['internationalName'] ?? 'Unknown';
        $ageCategory = $comp['ageCategory'] ?? 'N/A';
        $ageCategoryName = $comp['ageCategoryName'] ?? 'N/A';

        echo ($idx + 1) . ". {$name}\n";
        echo "   ageCategory: {$ageCategory}\n";
        echo "   ageCategoryName: {$ageCategoryName}\n";
        echo "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
