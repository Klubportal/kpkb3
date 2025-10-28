<?php

// SYNC FEHLENDE MATCH PLAYERS
echo "⏱️  SYNC FEHLENDE MATCH PLAYERS\n";
echo str_repeat("=", 60) . "\n\n";

// MySQL Verbindung
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// API Settings
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$teamFifaId = 598;

// Competition IDs wo NK Prigorje teilnimmt
$competitionsQuery = "
    SELECT DISTINCT competition_fifa_id
    FROM comet_matches
    WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}
";
$competitionsResult = $mysqli->query($competitionsQuery);
$compIds = [];
while ($row = $competitionsResult->fetch_assoc()) {
    $compIds[] = $row['competition_fifa_id'];
}
$compIdsList = implode(',', $compIds);

// Hole gespielt Matches OHNE Match Players
$matchesQuery = "
    SELECT m.match_fifa_id
    FROM comet_matches m
    LEFT JOIN comet_match_players mp ON m.match_fifa_id = mp.match_fifa_id
    WHERE m.competition_fifa_id IN ({$compIdsList})
      AND m.match_status = 'played'
      AND mp.id IS NULL
    ORDER BY m.match_fifa_id DESC
";
$matchesResult = $mysqli->query($matchesQuery);

if (!$matchesResult) {
    die("Query Error: " . $mysqli->error . "\n");
}

$matches = [];
while ($row = $matchesResult->fetch_assoc()) {
    $matches[] = $row['match_fifa_id'];
}

echo "📊 Matches OHNE Players gefunden: " . count($matches) . "\n";
echo "📊 Erwartete neue Einträge: ca. " . (count($matches) * 22) . "\n\n";

if (count($matches) == 0) {
    echo "✅ Alle gespielten Matches haben bereits Players!\n";
    $mysqli->close();
    exit(0);
}

echo "🚀 Starte Sync...\n\n";

$totalProcessed = 0;
$totalPlayers = 0;
$totalInserted = 0;
$errors = 0;
$noData = 0;

// Sync Match Players für jedes Match
foreach ($matches as $matchFifaId) {

    // Progress indicator every 50 matches
    if ($totalProcessed % 50 == 0 && $totalProcessed > 0) {
        echo "\n📊 Progress: {$totalProcessed}/" . count($matches) . " matches, {$totalInserted} players inserted...\n\n";
    }

    echo "📥 Match {$matchFifaId}...";

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
        echo " ❌ HTTP {$httpCode}\n";
        $errors++;
        $totalProcessed++;
        continue;
    }

    $players = json_decode($response, true);
    if (empty($players)) {
        echo " ⚠️  Keine Players\n";
        $noData++;
        $totalProcessed++;
        continue;
    }

    $playersInMatch = 0;

    foreach ($players as $playerData) {
        $playerFifaId = $playerData['playerFifaId'] ?? null;
        $teamFifaId = $playerData['teamFifaId'] ?? null;

        if (!$playerFifaId || !$teamFifaId) {
            continue;
        }

        $shirtNumber = $playerData['shirtNumber'] ?? null;
        $captain = isset($playerData['captain']) && $playerData['captain'] ? 1 : 0;
        $goalkeeper = isset($playerData['goalkeeper']) && $playerData['goalkeeper'] ? 1 : 0;
        $startingLineup = isset($playerData['startingLineup']) && $playerData['startingLineup'] ? 1 : 0;
        $played = isset($playerData['played']) && $playerData['played'] ? 1 : 0;

        // Insert into database
        $insertQuery = "INSERT INTO comet_match_players (
            match_fifa_id,
            player_fifa_id,
            team_fifa_id,
            shirt_number,
            captain,
            goalkeeper,
            starting_lineup,
            played,
            created_at,
            updated_at
        ) VALUES (
            {$matchFifaId},
            {$playerFifaId},
            {$teamFifaId},
            " . ($shirtNumber !== null ? $shirtNumber : "NULL") . ",
            {$captain},
            {$goalkeeper},
            {$startingLineup},
            {$played},
            NOW(),
            NOW()
        ) ON DUPLICATE KEY UPDATE
            shirt_number = VALUES(shirt_number),
            captain = VALUES(captain),
            goalkeeper = VALUES(goalkeeper),
            starting_lineup = VALUES(starting_lineup),
            played = VALUES(played),
            updated_at = NOW()";

        if ($mysqli->query($insertQuery)) {
            $totalInserted++;
            $playersInMatch++;
        }
    }

    echo " ✅ {$playersInMatch} Player(s)\n";
    $totalProcessed++;
    $totalPlayers += $playersInMatch++;

    // Kurze Pause
    usleep(100000); // 100ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ FEHLENDE MATCH PLAYERS SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Players eingefügt: {$totalInserted}\n";
echo "   - Keine Daten: {$noData}\n";
echo "   - Fehler: {$errors}\n\n";

// Finale Statistik
$finalCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_match_players")->fetch_assoc()['count'];
$matchesWithPlayers = $mysqli->query("SELECT COUNT(DISTINCT match_fifa_id) as count FROM comet_match_players")->fetch_assoc()['count'];

echo "📊 FINALE STATISTIK:\n";
echo "   - Total Match Player Einträge: {$finalCount}\n";
echo "   - Matches mit Players: {$matchesWithPlayers} / 504\n";
echo "   - Coverage: " . round(($matchesWithPlayers / 504) * 100, 1) . "%\n";

$mysqli->close();
