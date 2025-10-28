<?php

// SYNC FEHLENDE MATCH EVENTS
echo "⏱️  SYNC FEHLENDE MATCH EVENTS\n";
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

// Hole gespielte Matches OHNE Events
$matchesQuery = "
    SELECT m.match_fifa_id
    FROM comet_matches m
    LEFT JOIN comet_match_events me ON m.match_fifa_id = me.match_fifa_id
    WHERE m.competition_fifa_id IN ({$compIdsList})
      AND m.match_status = 'played'
      AND me.id IS NULL
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

echo "📊 Matches OHNE Events gefunden: " . count($matches) . "\n\n";

if (count($matches) == 0) {
    echo "✅ Alle gespielten Matches haben bereits Events!\n";
    $mysqli->close();
    exit(0);
}

echo "🚀 Starte Sync...\n\n";

$totalProcessed = 0;
$totalEvents = 0;
$totalInserted = 0;
$errors = 0;
$noData = 0;

// Sync Events für jedes Match
foreach ($matches as $matchFifaId) {

    // Progress indicator every 25 matches
    if ($totalProcessed % 25 == 0 && $totalProcessed > 0) {
        echo "\n📊 Progress: {$totalProcessed}/" . count($matches) . " matches, {$totalInserted} events inserted...\n\n";
    }

    echo "📥 Match {$matchFifaId}...";

    // API Call
    $ch = curl_init("{$apiUrl}/match/{$matchFifaId}/events");
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

    $events = json_decode($response, true);
    if (empty($events)) {
        echo " ⚠️  Keine Events\n";
        $noData++;
        $totalProcessed++;
        continue;
    }

    $eventsInMatch = 0;

    foreach ($events as $event) {
        $eventType = $event['event'] ?? null;
        $playerFifaId = $event['playerFifaId'] ?? null;
        $teamFifaId = $event['teamFifaId'] ?? null;
        $eventTime = $event['time'] ?? null;

        if (!$eventType) continue;

        $playerName = $mysqli->real_escape_string($event['playerName'] ?? '');
        $teamName = $mysqli->real_escape_string($event['teamName'] ?? '');

        // Insert
        $insertQuery = "INSERT INTO comet_match_events (
            match_fifa_id,
            event_type,
            player_fifa_id,
            player_name,
            team_fifa_id,
            team_name,
            event_time,
            created_at,
            updated_at
        ) VALUES (
            {$matchFifaId},
            '{$eventType}',
            " . ($playerFifaId ? $playerFifaId : "NULL") . ",
            '{$playerName}',
            " . ($teamFifaId ? $teamFifaId : "NULL") . ",
            '{$teamName}',
            " . ($eventTime ? $eventTime : "NULL") . ",
            NOW(),
            NOW()
        )";

        if ($mysqli->query($insertQuery)) {
            $totalInserted++;
            $eventsInMatch++;
        }
    }

    echo " ✅ {$eventsInMatch} Event(s)\n";
    $totalProcessed++;
    $totalEvents += $eventsInMatch;

    // Pause
    usleep(100000); // 100ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ FEHLENDE MATCH EVENTS SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Events eingefügt: {$totalInserted}\n";
echo "   - Keine Daten: {$noData}\n";
echo "   - Fehler: {$errors}\n\n";

// Finale Statistik
$finalCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_match_events")->fetch_assoc()['count'];
$matchesWithEvents = $mysqli->query("SELECT COUNT(DISTINCT match_fifa_id) as count FROM comet_match_events")->fetch_assoc()['count'];

// Events nach Typ
$typeQuery = "SELECT event_type, COUNT(*) as count FROM comet_match_events GROUP BY event_type ORDER BY count DESC";
$typeResult = $mysqli->query($typeQuery);

echo "📊 FINALE STATISTIK:\n";
echo "   - Total Events: {$finalCount}\n";
echo "   - Matches mit Events: {$matchesWithEvents} / 504\n\n";

echo "📈 EVENTS NACH TYP:\n";
echo str_repeat("-", 60) . "\n";
while ($row = $typeResult->fetch_assoc()) {
    echo sprintf("%-20s: %5d\n", $row['event_type'], $row['count']);
}

$mysqli->close();
