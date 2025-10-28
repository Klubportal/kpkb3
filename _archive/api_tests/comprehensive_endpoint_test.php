<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "🔍 UMFASSENDER TEST - ALLE MÖGLICHEN ENDPOINTS\n";
echo "   Players, Top Scorers, Lineups, Venues, Statistics\n";
echo str_repeat("═", 100) . "\n\n";

$apiBase = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

$competitionId = 100629221;
$matchId = 100629580;

// Alle möglichen Variationen
$allEndpoints = [
    // Players - verschiedene Variationen
    "/api/export/comet/competition/{cid}/players" => "Players (Standard)",
    "/api/export/comet/competition/{cid}/player" => "Player (Singular)",
    "/api/export/comet/competition/{cid}/player-list" => "Player List",
    "/api/export/comet/competition/{cid}/team/{teamId}/players" => "Players per Team",
    "/api/export/comet/players" => "All Players Global",
    "/api/export/comet/competition/{cid}/scorers" => "Top Scorers",
    "/api/export/comet/competition/{cid}/top-scorers" => "Top Scorers (Alt)",
    "/api/export/comet/competition/{cid}/scorer" => "Scorers",

    // Match Lineup
    "/api/export/comet/match/{mid}/lineup" => "Match Lineup (Singular)",
    "/api/export/comet/match/{mid}/lineups" => "Match Lineups (Plural)",
    "/api/export/comet/match/{mid}/team/{teamId}/lineup" => "Team Lineup",
    "/api/export/comet/match/{mid}/formation" => "Match Formation",

    // Statistics
    "/api/export/comet/competition/{cid}/statistics" => "Competition Statistics",
    "/api/export/comet/competition/{cid}/player-statistics" => "Player Statistics",
    "/api/export/comet/competition/{cid}/team-statistics" => "Team Statistics",
    "/api/export/comet/match/{mid}/statistics" => "Match Statistics",
    "/api/export/comet/match/{mid}/team-statistics" => "Match Team Statistics",

    // Venues
    "/api/export/comet/venues" => "All Venues",
    "/api/export/comet/competition/{cid}/venues" => "Competition Venues",
    "/api/export/comet/stadiums" => "Stadiums",
    "/api/export/comet/facilities" => "Facilities",

    // Teams
    "/api/export/comet/teams" => "All Teams",
    "/api/export/comet/competition/{cid}/teams" => "Competition Teams",
    "/api/export/comet/competition/{cid}/clubs" => "Clubs",
];

echo "【TESTE: Wettbewerb " . $competitionId . " | Match " . $matchId . "】\n\n";

$working = [];
$httpErrors = [];

foreach ($allEndpoints as $endpoint => $description) {
    // Replace placeholders
    $url = str_replace('{cid}', $competitionId, $endpoint);
    $url = str_replace('{mid}', $matchId, $url);
    $url = str_replace('{teamId}', '598', $url); // NK Prigorje
    $url = $apiBase . $url;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $count = is_array($data) ? count($data) : '?';

        echo sprintf("✅ [200] %-50s | %5s items\n", $description, $count);

        $working[] = [
            'endpoint' => $endpoint,
            'description' => $description,
            'url' => substr($url, strlen($apiBase)),
            'count' => $count,
            'sample' => is_array($data) && count($data) > 0 ? json_encode($data[0], JSON_UNESCAPED_UNICODE) : null
        ];
    } else {
        $shortEndpoint = strlen($endpoint) > 50 ? substr($endpoint, 0, 47) . "..." : $endpoint;
        echo sprintf("❌ [%d] %s\n", $httpCode, $description);

        if (!isset($httpErrors[$httpCode])) {
            $httpErrors[$httpCode] = [];
        }
        $httpErrors[$httpCode][] = $description;
    }
}

echo "\n" . str_repeat("═", 100) . "\n";
echo "【ZUSAMMENFASSUNG】\n";
echo str_repeat("═", 100) . "\n\n";

echo "✅ FUNKTIONIERT (" . count($working) . " Endpoints):\n\n";

foreach ($working as $ep) {
    echo sprintf("📌 %s\n", $ep['description']);
    echo sprintf("   Endpoint: %s\n", $ep['endpoint']);
    echo sprintf("   Items: %s\n", $ep['count']);

    if ($ep['sample']) {
        $samplePreview = substr($ep['sample'], 0, 120);
        echo sprintf("   Sample: %s...\n", $samplePreview);
    }
    echo "\n";
}

if (empty($working)) {
    echo "❌ KEINE FUNKTIONIERENDEN ENDPOINTS GEFUNDEN\n\n";
}

echo "❌ FEHLER NACH HTTP CODE:\n";
foreach ($httpErrors as $code => $descriptions) {
    echo sprintf("\n   HTTP %d (%d Endpoints):\n", $code, count($descriptions));
    foreach ($descriptions as $desc) {
        echo sprintf("     • %s\n", $desc);
    }
}

echo "\n" . str_repeat("═", 100) . "\n\n";

?>
