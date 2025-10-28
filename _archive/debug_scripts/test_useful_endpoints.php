<?php

// Test verschiedene Comet Endpoints
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

$testMatchId = 102984019; // Ein NK Prigorje Match
$testTeamId = 598; // NK Prigorje
$testCompId = 100629221; // PRVA ZAGREBAČKA LIGA

echo "🧪 TESTE COMET API ENDPOINTS\n";
echo str_repeat("=", 60) . "\n\n";

function testEndpoint($url, $username, $password, $description) {
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

            // Zeige erste paar Keys
            if (is_array($data)) {
                $first = is_array($data) && count($data) > 0 ? $data[0] : $data;
                if (is_array($first)) {
                    $keys = array_slice(array_keys($first), 0, 8);
                    echo "   📋 Keys: " . implode(', ', $keys) . "...\n";
                }
            }
            return true;
        } else {
            echo "   ⚠️  Status {$httpCode} - Keine Daten\n";
            return false;
        }
    } else {
        echo "   ❌ Status {$httpCode}\n";
        return false;
    }
    echo "\n";
}

echo "🏟️  TEAM DETAILS:\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/team/{$testTeamId}", $username, $password, "Team Details");
echo "\n";

echo "📊 MATCH STATISTICS:\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/match/{$testMatchId}/statistics", $username, $password, "Match Statistics");
echo "\n";

echo "👥 MATCH OFFICIALS (Schiedsrichter):\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/match/{$testMatchId}/officials", $username, $password, "Match Officials");
echo "\n";

echo "📊 TEAM STATISTICS PRO COMPETITION:\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/competition/{$testCompId}/{$testTeamId}/statistics", $username, $password, "Team Competition Statistics");
echo "\n";

echo "🏆 COMPETITION DETAILS:\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/competition/{$testCompId}", $username, $password, "Competition Details");
echo "\n";

echo "📋 COMPETITION GROUPS:\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/competition/{$testCompId}/groups", $username, $password, "Competition Groups");
echo "\n";

echo "🎯 COMPETITION ROUNDS:\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/competition/{$testCompId}/rounds", $username, $password, "Competition Rounds");
echo "\n";

echo "⚽ MATCH LINEUP:\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/match/{$testMatchId}/lineup", $username, $password, "Match Lineup");
echo "\n";

echo "📈 PLAYER DETAILS:\n";
echo str_repeat("-", 60) . "\n";
testEndpoint("{$apiUrl}/player/19086702", $username, $password, "Player Details (Dominik Baron)");
echo "\n";
