<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;

echo "【 COMET API - Age Categories in All Languages 】\n";
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

    echo "All Available Fields in Competition Object:\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    // Show first competition with all fields
    if (count($competitions) > 0) {
        $firstComp = $competitions[0];
        echo "Sample Competition #1: " . $firstComp['internationalName'] . "\n\n";

        foreach ($firstComp as $key => $value) {
            $type = gettype($value);
            if ($type === 'array') {
                echo "{$key}: ARRAY\n";
                if (is_array($value) && count($value) > 0) {
                    if (isset($value[0]) && is_array($value[0])) {
                        echo "  First item keys: " . implode(', ', array_keys($value[0])) . "\n";
                        print_r(array_slice($value, 0, 1));
                    } else {
                        echo "  Value: " . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                    }
                }
            } elseif ($type === 'object') {
                echo "{$key}: OBJECT\n";
            } else {
                echo "{$key}: {$value}\n";
            }
        }

        echo "\n════════════════════════════════════════════════════════════════\n\n";

        // Check localNames field specifically
        if (isset($firstComp['localNames']) && is_array($firstComp['localNames'])) {
            echo "localNames Field (Available Languages):\n";
            echo "════════════════════════════════════════════════════════════════\n\n";

            foreach ($firstComp['localNames'] as $localName) {
                echo "Language: " . ($localName['language'] ?? 'N/A') . "\n";
                echo "  Name: " . ($localName['name'] ?? 'N/A') . "\n";
                echo "  Short Name: " . ($localName['shortName'] ?? 'N/A') . "\n";
                echo "\n";
            }
        }
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
