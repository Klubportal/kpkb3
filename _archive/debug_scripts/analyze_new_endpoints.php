<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "📊 DETAILLIERTE STRUKTUR DER NEUEN ENDPOINTS\n";
echo str_repeat("═", 100) . "\n\n";

$apiBase = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

$competitionId = 100629221;
$teamId = 622; // NK Botinec
$matchId = 100629580;

// 1. Team Players
echo "【1】 Team Players - Spieler eines Teams\n";
echo str_repeat("─", 100) . "\n\n";

$url = $apiBase . '/api/export/comet/team/' . $teamId . '/players';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

$players = json_decode($response, true);

echo "✅ " . count($players) . " Spieler abgerufen\n\n";

if (count($players) > 0) {
    echo "📌 Erste 3 Spieler:\n";
    for ($i = 0; $i < min(3, count($players)); $i++) {
        echo "\n--- Spieler " . ($i + 1) . " ---\n";
        echo json_encode($players[$i], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
}

// 2. Matches mit Lineups
echo "\n\n【2】 Matches mit Lineups\n";
echo str_repeat("─", 100) . "\n\n";

$url = $apiBase . '/api/export/comet/competition/' . $competitionId . '/matches?includeLineups=true';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

$matchesWithLineups = json_decode($response, true);

echo "✅ " . count($matchesWithLineups) . " Matches mit Lineups abgerufen\n\n";

if (count($matchesWithLineups) > 0) {
    // Find a match with lineup data
    $matchWithData = null;
    foreach ($matchesWithLineups as $m) {
        if (isset($m['matchLineup']) || isset($m['lineups']) || isset($m['matchTeams'])) {
            $matchWithData = $m;
            break;
        }
    }

    if ($matchWithData) {
        echo "📌 Match Struktur mit Lineup-Daten:\n";
        $preview = json_encode($matchWithData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo substr($preview, 0, 1500) . "...\n";
    }
}

// 3. Single Match mit Lineup
echo "\n\n【3】 Einzelnes Match mit Lineup\n";
echo str_repeat("─", 100) . "\n\n";

$url = $apiBase . '/api/export/comet/match/' . $matchId . '?includeLineup=true';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

$matchData = json_decode($response, true);

if (is_array($matchData)) {
    echo "✅ Match mit Lineup abgerufen\n\n";

    echo "📌 Match Struktur (erste 2000 Zeichen):\n";
    $preview = json_encode($matchData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo substr($preview, 0, 2000) . "...\n";
} else {
    echo "❌ Fehler beim Abrufen\n";
}

echo "\n" . str_repeat("═", 100) . "\n\n";

?>
