<?php

// Test API Response für Team Players
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$teamFifaId = 598;

echo "🔍 TEST TEAM PLAYERS API - Team {$teamFifaId}\n";
echo str_repeat("=", 60) . "\n\n";

// API Call
$ch = curl_init("{$apiUrl}/team/{$teamFifaId}/players?status=ACTIVE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    die("❌ API Fehler: HTTP {$httpCode}\n");
}

$players = json_decode($response, true);

echo "📊 Anzahl aktive Spieler: " . count($players) . "\n\n";

if (!empty($players)) {
    echo "📄 Erster Spieler (vollständige Struktur):\n";
    echo str_repeat("-", 60) . "\n";
    print_r($players[0]);

    echo "\n\n📋 Alle verfügbaren Felder:\n";
    echo str_repeat("-", 60) . "\n";
    $fields = array_keys($players[0]);
    foreach ($fields as $field) {
        $value = $players[0][$field];
        $type = gettype($value);
        $display = is_null($value) ? 'NULL' : (is_array($value) ? json_encode($value) : $value);
        echo sprintf("%-30s | %-10s | %s\n", $field, $type, $display);
    }

    echo "\n\n📊 Spieler-Übersicht (erste 10):\n";
    echo str_repeat("-", 60) . "\n";
    foreach (array_slice($players, 0, 10) as $i => $player) {
        echo ($i + 1) . ". ";
        echo ($player['internationalFirstName'] ?? '') . " " . ($player['internationalLastName'] ?? '');
        echo " - " . ($player['playerPosition'] ?? '?');
        echo " - #" . ($player['shirtNumber'] ?? '?');
        echo " - " . ($player['dateOfBirth'] ?? '?');
        echo "\n";
    }
}
