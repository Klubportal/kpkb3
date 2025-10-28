<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;

echo "【 COMET API - All Languages Across All Competitions 】\n";
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

    $allLanguages = [];

    foreach ($competitions as $comp) {
        if (isset($comp['localNames']) && is_array($comp['localNames'])) {
            foreach ($comp['localNames'] as $localName) {
                $lang = $localName['language'] ?? 'UNKNOWN';
                if (!isset($allLanguages[$lang])) {
                    $allLanguages[$lang] = [];
                }
                $allLanguages[$lang][] = [
                    'comp' => $comp['internationalName'],
                    'name' => $localName['name'],
                    'shortName' => $localName['shortName'],
                ];
            }
        }
    }

    echo "Languages Found: " . implode(', ', array_keys($allLanguages)) . "\n\n";

    foreach ($allLanguages as $langCode => $entries) {
        echo "【 Language: {$langCode} 】\n";
        echo "════════════════════════════════════════════════════════════════\n\n";

        foreach (array_slice($entries, 0, 3) as $entry) {
            echo "Competition: " . $entry['comp'] . "\n";
            echo "  Name: " . $entry['name'] . "\n";
            echo "  Short: " . $entry['shortName'] . "\n\n";
        }
        echo "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
