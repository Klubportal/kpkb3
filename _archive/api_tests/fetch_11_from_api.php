<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔌 FETCH 11 ACTIVE COMPETITIONS FOR NK PRIGORJE (FIFA 598)\n";
echo "   Using: Real Comet REST API (kp_api credentials)\n";
echo str_repeat("═", 80) . "\n\n";

// Get credentials from env
$baseUrl = env('COMET_API_BASE_URL', 'https://api-hns.analyticom.de');
$username = env('COMET_API_USERNAME', 'nkprigorje');
$password = env('COMET_API_PASSWORD', '3c6nR$dS');
$fifaId = 598;

echo "📡 API Configuration:\n";
echo "   Base URL: $baseUrl\n";
echo "   Username: $username\n";
echo "   Password: " . substr($password, 0, 3) . "***\n";
echo "   Club FIFA ID: $fifaId\n\n";

// Create basic auth header
$credentials = base64_encode("$username:$password");

try {
    echo "⏳ Connecting to Comet API...\n\n";

    // Try different endpoints to get competitions
    $endpoints = [
        "/club/$fifaId/competitions",
        "/clubs/$fifaId/competitions",
        "/teams/$fifaId/competitions",
        "/organization/$fifaId/competitions",
    ];

    $response = null;
    $successUrl = null;

    foreach ($endpoints as $endpoint) {
        try {
            $url = $baseUrl . $endpoint;
            echo "  Trying: $url\n";

            $resp = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Basic ' . $credentials,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get($url);

            if ($resp->successful()) {
                $response = $resp;
                $successUrl = $url;
                echo "  ✅ Success!\n\n";
                break;
            } else {
                echo "  ❌ Status: " . $resp->status() . "\n";
            }
        } catch (\Exception $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
        }
    }

    if ($response && $response->successful()) {
        $data = $response->json();

        echo str_repeat("═", 80) . "\n";
        echo "✅ SUCCESS! Connected to: $successUrl\n";
        echo str_repeat("─", 80) . "\n\n";

        if (is_array($data)) {
            echo "📊 COMPETITIONS RECEIVED: " . count($data) . "\n\n";

            // Check if it's paginated
            if (isset($data['data']) && is_array($data['data'])) {
                $competitions = $data['data'];
                echo "📊 (Paginated) Total: " . count($competitions) . "\n\n";
            } else {
                $competitions = $data;
            }

            // Display all competitions
            foreach ($competitions as $i => $comp) {
                echo "【" . ($i + 1) . "】 " . ($comp['name'] ?? $comp['international_competition_name'] ?? $comp['competitionName'] ?? 'Unknown') . "\n";

                // Print all available fields
                foreach ($comp as $key => $value) {
                    if (!is_array($value) && !is_object($value)) {
                        echo "     $key: " . substr(json_encode($value), 0, 60) . "\n";
                    }
                }
                echo "\n";
            }

            // Count competitions
            echo str_repeat("═", 80) . "\n";
            if (count($competitions) >= 11) {
                echo "✅ FOUND " . count($competitions) . " COMPETITIONS (>= 11)\n";
            } else {
                echo "⚠️  Found " . count($competitions) . " competitions (expected 11)\n";
            }
            echo str_repeat("═", 80) . "\n\n";

        } else {
            echo "Response structure:\n";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        }

    } else {
        echo "❌ All endpoints failed\n";
        echo "Response: " . $response?->body() . "\n\n";
    }

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n\n";
}

echo str_repeat("═", 80) . "\n";
