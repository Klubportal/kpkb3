<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use App\Models\CometCompetition;

const CLUB_FIFA_ID = 598;

echo "【 Fetching REAL Competitions from Comet API 】\n";
echo "   Where Club 598 participates + ACTIVE + Season 2025/26\n";
echo "════════════════════════════════════════════════════════════════\n";

// Comet API credentials
$apiUrl = 'https://api-hns.analyticom.de';
$apiUsername = 'nkprigorje';
$apiPassword = '3c6nR$dS';

$client = new Client([
    'auth' => [$apiUsername, $apiPassword],
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => false,
]);

echo "\n【 Step 1: Query Available Endpoints 】\n";

// Try to find competitions endpoint
$endpointsToTry = [
    '/clubs/' . CLUB_FIFA_ID . '/competitions',
    '/club/' . CLUB_FIFA_ID . '/competitions',
    '/club/' . CLUB_FIFA_ID,
    '/matches?club=' . CLUB_FIFA_ID,
    '/competitions?club=' . CLUB_FIFA_ID,
];

$competitionsData = [];

foreach ($endpointsToTry as $endpoint) {
    try {
        echo "📡 Trying: GET {$apiUrl}{$endpoint}\n";

        $response = $client->request('GET', $apiUrl . $endpoint, [
            'query' => [
                'season' => '2025',
                'status' => 'ACTIVE',
                'limit' => 100,
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data && (is_array($data) || is_object($data))) {
            echo "✅ Success! Retrieved data:\n";

            // Pretty print
            if (is_array($data)) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            $competitionsData = $data;
            break;

        }

    } catch (\Exception $e) {
        $statusCode = method_exists($e, 'getCode') ? $e->getCode() : 'unknown';
        echo "⚠️  Failed ({$statusCode})\n";
    }

    sleep(1); // Rate limiting
}

if (empty($competitionsData)) {
    echo "\n❌ Could not fetch from any endpoint!\n";
    echo "\n【 Alternative: Extracting from stored match data 】\n";

    // Use fallback
    require __DIR__ . '/sync_11_real_competitions.php';
}
