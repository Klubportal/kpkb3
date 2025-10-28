<?php

// Test COMET API mit teamFifaId Parameter
$username = 'nkprigorje';
$password = '3c6nR$dS';

echo "=== Test: COMET API Competitions mit teamFifaId Parameter ===\n\n";

foreach ([598, 396] as $teamId) {
    echo "Club FIFA ID: {$teamId}\n";
    echo str_repeat('-', 60) . "\n";

    $url = "https://api-hns.analyticom.de/api/export/comet/competitions?teamFifaId={$teamId}&active=true";
    echo "URL: {$url}\n";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: {$httpCode}\n";

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && is_array($data)) {
            echo "✅ Competitions gefunden: " . count($data) . "\n";

            if (count($data) > 0) {
                echo "\nErste 3 Competitions:\n";
                foreach (array_slice($data, 0, 3) as $comp) {
                    echo "  - {$comp['internationalName']} (Season: {$comp['season']}, ID: {$comp['competitionFifaId']})\n";
                }
            } else {
                echo "⚠️  Keine Competitions für diesen Club\n";
            }
        } else {
            echo "❌ Ungültige Antwort\n";
        }
    } else {
        echo "❌ Fehler: HTTP {$httpCode}\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }

    echo "\n" . str_repeat('=', 60) . "\n\n";
}
