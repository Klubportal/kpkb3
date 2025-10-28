<?php

// Test Match Players API
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$matchFifaId = 100629577;

echo "🔍 TEST MATCH PLAYERS API - Match {$matchFifaId}\n";
echo str_repeat("=", 60) . "\n\n";

// API Call
$ch = curl_init("{$apiUrl}/match/{$matchFifaId}/players");
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

$data = json_decode($response, true);

echo "📊 API Response Struktur:\n";
echo str_repeat("-", 60) . "\n";
print_r($data);

if (!empty($data)) {
    echo "\n\n📋 Verfügbare Felder im ersten Eintrag:\n";
    echo str_repeat("-", 60) . "\n";

    $firstItem = is_array($data[0]) ? $data[0] : $data;
    foreach ($firstItem as $key => $value) {
        $type = gettype($value);
        $display = is_null($value) ? 'NULL' : (is_array($value) ? 'ARRAY' : $value);
        echo sprintf("%-30s | %-10s | %s\n", $key, $type, $display);
    }
}
