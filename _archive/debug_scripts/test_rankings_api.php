<?php

echo "🔍 CHECKING COMET RANKINGS API RESPONSE\n";
echo str_repeat("=", 80) . "\n\n";

$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Get one competition's rankings
$competitionId = 100629221; // From database

$ch = curl_init("{$apiUrl}/competition/{$competitionId}/ranking");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    die("❌ HTTP {$httpCode}\n");
}

$rankings = json_decode($response, true);

echo "📊 Raw response:\n";
echo $response . "\n\n";

if (empty($rankings)) {
    echo "⚠️  No rankings data or empty response\n";
    exit;
}

echo "📊 Found " . count($rankings) . " ranking entries\n\n";
echo "📋 First ranking entry structure:\n";
echo str_repeat("-", 80) . "\n";
print_r($rankings[0]);
echo "\n";

echo "📋 Second ranking entry structure:\n";
echo str_repeat("-", 80) . "\n";
print_r($rankings[1]);
