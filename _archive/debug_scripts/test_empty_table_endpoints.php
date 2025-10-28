<?php

$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

$testMatchId = 102984019;
$testTeamId = 598;
$testCompId = 100629221;

echo "🧪 TESTE ENDPOINTS FÜR LEERE TABELLEN\n";
echo str_repeat("=", 60) . "\n\n";

function testApi($url, $username, $password, $description) {
    echo "📡 {$description}\n";
    echo "   URL: {$url}\n";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data) {
            $count = is_array($data) ? count($data) : 1;
            echo "   ✅ Status {$httpCode} - {$count} Einträge\n";

            // Zeige erste Daten
            if (is_array($data) && count($data) > 0) {
                $first = $data[0];
                if (is_array($first)) {
                    echo "   📋 Beispiel-Keys: " . implode(', ', array_slice(array_keys($first), 0, 5)) . "...\n";
                    echo "   📄 Erste Zeile: " . json_encode($first, JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
            return true;
        }
    }
    echo "   ❌ Status {$httpCode}\n";
    return false;
}

echo "1️⃣  MATCH TEAM OFFICIALS (Trainer pro Match):\n";
echo str_repeat("-", 60) . "\n";
testApi("{$apiUrl}/match/{$testMatchId}/teamOfficials", $username, $password, "Match Team Officials");
echo "\n\n";

echo "2️⃣  COMPETITION PLAYER STATS (Spieler-Stats pro Wettbewerb):\n";
echo str_repeat("-", 60) . "\n";
testApi("{$apiUrl}/competition/{$testCompId}/{$testTeamId}/players", $username, $password, "Competition Players Stats");
echo "\n\n";

echo "3️⃣  FACILITIES (Stadien/Spielstätten):\n";
echo str_repeat("-", 60) . "\n";
// Test verschiedene Facility Endpoints
$facilityTests = [
    "/facilities",
    "/team/{$testTeamId}/facilities",
    "/facility/1",
    "/organisation/598/facilities"
];

foreach ($facilityTests as $endpoint) {
    $url = $apiUrl . $endpoint;
    testApi($url, $username, $password, "Facilities: {$endpoint}");
    echo "\n";
}
