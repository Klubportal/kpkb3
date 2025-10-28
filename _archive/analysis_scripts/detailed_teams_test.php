<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "🎯 TEAMS ENDPOINT DETAILLIERT TESTEN + WEITERE VARIATIONEN\n";
echo str_repeat("═", 100) . "\n\n";

$apiBase = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

$competitionId = 100629221;

// 1. Teams abrufen - Struktur verstehen
echo "【1】 Competition Teams abrufen\n";
echo str_repeat("─", 100) . "\n\n";

$url = $apiBase . '/api/export/comet/competition/' . $competitionId . '/teams';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $teams = json_decode($response, true);
    echo "✅ " . count($teams) . " Teams abgerufen\n\n";

    if (is_array($teams) && count($teams) > 0) {
        echo "📌 Erste Team Struktur:\n";
        echo json_encode($teams[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        // Prüfe auf Team-spezifische Endpoints
        $firstTeam = $teams[0];
        $teamId = $firstTeam['teamFifaId'] ?? null;

        if ($teamId) {
            echo "【2】 Teste Team-spezifische Endpoints (Team ID: " . $teamId . ")\n";
            echo str_repeat("─", 100) . "\n\n";

            $teamEndpoints = [
                "/api/export/comet/team/{teamId}" => "Team Details",
                "/api/export/comet/team/{teamId}/players" => "Team Players",
                "/api/export/comet/team/{teamId}/squad" => "Team Squad",
                "/api/export/comet/team/{teamId}/roster" => "Team Roster",
                "/api/export/comet/competition/{cid}/team/{teamId}" => "Team im Wettbewerb",
                "/api/export/comet/competition/{cid}/team/{teamId}/players" => "Team Players im Wettbewerb",
                "/api/export/comet/competition/{cid}/team/{teamId}/statistics" => "Team Statistiken",
                "/api/export/comet/team/{teamId}/statistics" => "Team Statistics (Global)",
            ];

            foreach ($teamEndpoints as $endpoint => $description) {
                $testUrl = str_replace('{teamId}', $teamId, $endpoint);
                $testUrl = str_replace('{cid}', $competitionId, $testUrl);
                $testUrl = $apiBase . $testUrl;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $testUrl);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $testResponse = curl_exec($ch);
                $testHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($testHttpCode === 200) {
                    $data = json_decode($testResponse, true);
                    $count = is_array($data) ? count($data) : '?';
                    echo sprintf("✅ [200] %-50s | %s items\n", $description, $count);
                } else {
                    echo sprintf("❌ [%d] %s\n", $testHttpCode, $description);
                }
            }
        }
    }
} else {
    echo "❌ Fehler beim Abrufen (HTTP " . $httpCode . ")\n";
}

echo "\n" . str_repeat("═", 100) . "\n";
echo "【3】 Weitere kreative Endpoint-Tests\n";
echo str_repeat("─", 100) . "\n\n";

$creativeEndpoints = [
    "/api/export/comet/competition/{cid}/matches?includeLineups=true" => "Matches mit Lineups",
    "/api/export/comet/match/{mid}?includeLineup=true" => "Match mit Lineup",
    "/api/export/comet/competition/{cid}/top-scorers" => "Top Scorers (Alt 1)",
    "/api/export/comet/competition/{cid}/stats/scorers" => "Stats Scorers",
    "/api/export/comet/competition/{cid}/standings?extended=true" => "Extended Standings",
];

$matchId = 100629580;

foreach ($creativeEndpoints as $endpoint => $description) {
    $testUrl = str_replace('{cid}', $competitionId, $endpoint);
    $testUrl = str_replace('{mid}', $matchId, $testUrl);
    $testUrl = $apiBase . $testUrl;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $testResponse = curl_exec($ch);
    $testHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($testHttpCode === 200) {
        $data = json_decode($testResponse, true);
        $count = is_array($data) ? count($data) : '?';
        echo sprintf("✅ [200] %-45s | %s items\n", $description, $count);
    } else {
        echo sprintf("❌ [%d] %s\n", $testHttpCode, $description);
    }
}

echo "\n" . str_repeat("═", 100) . "\n\n";

?>
