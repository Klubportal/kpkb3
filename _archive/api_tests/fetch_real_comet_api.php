<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * REAL COMET API - FETCH COMPETITIONS FOR CLUB FIFA ID 598
 *
 * Requirements:
 * 1. Comet API Base URL: https://api.soccer.sportdata.de/v2.0/
 * 2. API Key from .env or config
 * 3. Club FIFA ID: 598 (NK Prigorje)
 */

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔌 REAL COMET API - Fetch All Active Competitions\n";
echo "   Club: NK Prigorje Markušević (FIFA ID: 598)\n";
echo str_repeat("═", 80) . "\n\n";

$fifaId = 598;
$baseUrl = 'https://api.soccer.sportdata.de/v2.0/';
$apiKey = config('services.comet.api_key') ?? env('COMET_API_KEY');

if (!$apiKey) {
    echo "⚠️  WARNING: No Comet API Key found in config!\n\n";
    echo "To use the real Comet API, you need:\n";
    echo "1. Add COMET_API_KEY to .env file\n";
    echo "2. Or add to config/services.php:\n";
    echo "   'comet' => ['api_key' => env('COMET_API_KEY')]\n\n";
    echo "Current config value: " . ($apiKey ? "SET ✓" : "NOT SET ✗") . "\n\n";

    // Try to get test data anyway
    echo "Attempting to fetch from public API endpoint...\n\n";
}

// Build request
$endpoint = "clubs/{$fifaId}/competitions";
$url = $baseUrl . $endpoint;

echo "📡 API Request:\n";
echo "   Method: GET\n";
echo "   URL: " . $url . "\n";
echo "   Headers: X-API-Key: " . (substr($apiKey, 0, 5) ?? "NOT SET") . "***\n\n";

try {
    echo "⏳ Connecting to Comet API...\n\n";

    $response = Http::timeout(30)
        ->withHeaders([
            'X-API-Key' => $apiKey,
            'Accept' => 'application/json',
        ])
        ->get($url, [
            'season' => 2024,
            'status' => 'active'
        ]);

    echo "📊 Response Status: " . $response->status() . "\n\n";

    if ($response->successful()) {
        $data = $response->json();

        echo "✅ SUCCESS! Received data from Comet API\n";
        echo str_repeat("─", 80) . "\n\n";

        if (is_array($data) && count($data) > 0) {
            echo "📋 COMPETITIONS (" . count($data) . "):\n\n";

            foreach ($data as $i => $comp) {
                echo "[" . ($i + 1) . "] " . ($comp['name'] ?? $comp['competition_name'] ?? 'Unknown') . "\n";

                // Print all fields
                foreach ($comp as $key => $value) {
                    if (!is_array($value) && !is_object($value)) {
                        echo "     $key: " . (is_null($value) ? 'null' : $value) . "\n";
                    }
                }
                echo "\n";
            }
        } else {
            echo "📭 No competitions returned from API\n";
            echo "Full Response:\n";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

    } elseif ($response->status() === 401) {
        echo "❌ ERROR 401: Unauthorized\n";
        echo "   The API key is invalid or missing.\n";
        echo "   Response: " . $response->body() . "\n\n";

    } elseif ($response->status() === 404) {
        echo "❌ ERROR 404: Not Found\n";
        echo "   Club FIFA ID 598 not found in Comet API\n";
        echo "   Response: " . $response->body() . "\n\n";

    } else {
        echo "❌ ERROR " . $response->status() . "\n";
        echo "   Response: " . $response->body() . "\n\n";
    }

} catch (\Exception $e) {
    echo "❌ CONNECTION ERROR:\n";
    echo "   " . $e->getMessage() . "\n\n";
    echo "Possible causes:\n";
    echo "   1. Comet API server is down\n";
    echo "   2. Network connection issues\n";
    echo "   3. Invalid API endpoint\n";
    echo "   4. API key is invalid\n\n";
}

echo str_repeat("═", 80) . "\n";
echo "\n💡 NEXT STEPS:\n";
echo "   1. Get a valid Comet API key from sportdata.de\n";
echo "   2. Add it to .env as: COMET_API_KEY=your_key_here\n";
echo "   3. Run this script again to fetch real data\n";
echo "\n";
