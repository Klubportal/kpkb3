<?php

// Test COMET API for different clubs
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Test both 396 and 598
$clubsToTest = [396, 598];

$clubsToTest = [396, 598];

foreach ($clubsToTest as $clubFifaId) {
    echo "\n========== CLUB FIFA ID: {$clubFifaId} ==========\n";

    $apiUrls = [
        'matches' => "https://api-hns.analyticom.de/api/export/comet/club/{$clubFifaId}/matches",
    ];

    foreach ($apiUrls as $type => $url) {
        echo "\n=== Testing {$type} API ===\n";
        echo "URL: {$url}\n";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "HTTP Code: {$httpCode}\n";

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data) {
                echo "✅ Response: " . count($data) . " records\n";
            } else {
                echo "❌ Invalid JSON response\n";
            }
        } else {
            echo "❌ API returned error\n";
        }
    }
}
