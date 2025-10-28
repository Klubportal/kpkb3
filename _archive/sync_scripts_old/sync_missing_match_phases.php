<?php

// SYNC FEHLENDE MATCH PHASES
echo "⏱️  SYNC FEHLENDE MATCH PHASES\n";
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

// Hole alle Competition IDs wo NK Prigorje teilnimmt
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

echo "📊 NK Prigorje nimmt an " . count($compIds) . " Wettbewerben teil\n\n";

// Hole alle gespielten Matches aus diesen Competitions die NOCH KEINE Phases haben
$matchesQuery = "
    SELECT m.match_fifa_id, m.team_fifa_id_home, m.team_fifa_id_away,
           m.international_competition_name, m.match_status
    FROM comet_matches m
    LEFT JOIN comet_match_phases p ON m.match_fifa_id = p.match_fifa_id
    WHERE m.competition_fifa_id IN ({$compIdsList})
      AND m.match_status = 'played'
      AND p.id IS NULL
    ORDER BY m.match_fifa_id DESC
";
$matchesResult = $mysqli->query($matchesQuery);

if (!$matchesResult) {
    die("Query Error: " . $mysqli->error . "\n");
}

$matches = [];
while ($row = $matchesResult->fetch_assoc()) {
    $matches[] = $row;
}

echo "📊 Gespielte Matches OHNE Phases gefunden: " . count($matches) . "\n";
echo "📊 Erwartete neue Phases: " . (count($matches) * 2) . "\n\n";

if (count($matches) == 0) {
    echo "✅ Alle gespielten Matches haben bereits Phases!\n";
    $mysqli->close();
    exit(0);
}

echo "🚀 Starte Sync...\n\n";

$totalProcessed = 0;
$totalPhases = 0;
$totalInserted = 0;
$errors = 0;

// Sync Match Phases für jedes Match
foreach ($matches as $match) {
    $matchFifaId = $match['match_fifa_id'];

    // Progress indicator every 50 matches
    if ($totalProcessed % 50 == 0 && $totalProcessed > 0) {
        echo "\n📊 Progress: {$totalProcessed}/" . count($matches) . " matches processed, {$totalInserted} phases inserted...\n\n";
    }

    echo "📥 Match {$matchFifaId}...";

    // API Call
    $ch = curl_init("{$apiUrl}/match/{$matchFifaId}/phases");
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

    $phases = json_decode($response, true);
    if (empty($phases)) {
        echo " ⚠️  Keine Phases\n";
        $totalProcessed++;
        continue;
    }

    $phasesInMatch = 0;

    foreach ($phases as $phaseData) {
        $phase = strtoupper($phaseData['phase'] ?? '');
        $homeScore = $phaseData['homeScore'] ?? null;
        $awayScore = $phaseData['awayScore'] ?? null;
        $regularTime = $phaseData['regularTime'] ?? null;
        $stoppageTime = $phaseData['stoppageTime'] ?? null;
        $phaseLength = $phaseData['phaseLength'] ?? null;
        $startDateTime = $phaseData['startDateTime'] ?? null;
        $endDateTime = $phaseData['endDateTime'] ?? null;

        // Insert into database
        $insertQuery = "INSERT INTO comet_match_phases (
            match_fifa_id,
            phase,
            home_score,
            away_score,
            regular_time,
            stoppage_time,
            phase_length,
            start_date_time,
            end_date_time,
            created_at,
            updated_at
        ) VALUES (
            {$matchFifaId},
            " . ($phase ? "'{$phase}'" : "NULL") . ",
            " . ($homeScore !== null ? $homeScore : "NULL") . ",
            " . ($awayScore !== null ? $awayScore : "NULL") . ",
            " . ($regularTime !== null ? $regularTime : "NULL") . ",
            " . ($stoppageTime !== null ? $stoppageTime : "NULL") . ",
            " . ($phaseLength !== null ? $phaseLength : "NULL") . ",
            " . ($startDateTime ? "'{$startDateTime}'" : "NULL") . ",
            " . ($endDateTime ? "'{$endDateTime}'" : "NULL") . ",
            NOW(),
            NOW()
        )";

        if ($mysqli->query($insertQuery)) {
            $totalInserted++;
            $phasesInMatch++;
        }
    }

    echo " ✅ {$phasesInMatch} Phase(s)\n";
    $totalProcessed++;
    $totalPhases += $phasesInMatch;

    // Kurze Pause um API nicht zu überlasten
    usleep(100000); // 100ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ FEHLENDE MATCH PHASES SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Phases eingefügt: {$totalInserted}\n";
echo "   - Fehler: {$errors}\n\n";

// Zeige finale Statistik
$finalStatsQuery = "
    SELECT
        COUNT(DISTINCT m.match_fifa_id) as total_played,
        COUNT(DISTINCT p.match_fifa_id) as with_phases
    FROM comet_matches m
    LEFT JOIN comet_match_phases p ON m.match_fifa_id = p.match_fifa_id
    WHERE m.competition_fifa_id IN ({$compIdsList})
      AND m.match_status = 'played'
";
$finalStats = $mysqli->query($finalStatsQuery)->fetch_assoc();

$totalPhasesQuery = "SELECT COUNT(*) as count FROM comet_match_phases";
$totalPhasesCount = $mysqli->query($totalPhasesQuery)->fetch_assoc()['count'];

echo "📊 FINALE STATISTIK:\n";
echo "   - Gespielte Matches in NK Prigorje Competitions: {$finalStats['total_played']}\n";
echo "   - Matches mit Phases: {$finalStats['with_phases']}\n";
echo "   - Coverage: " . round(($finalStats['with_phases'] / max($finalStats['total_played'], 1)) * 100, 1) . "%\n";
echo "   - Total Phases in DB: {$totalPhasesCount}\n";
echo "   - Erwartet: " . ($finalStats['total_played'] * 2) . " phases\n";

if ($finalStats['with_phases'] == $finalStats['total_played']) {
    echo "\n🎉 PERFEKT! Alle gespielten Matches haben jetzt Phases!\n";
} else {
    $missing = $finalStats['total_played'] - $finalStats['with_phases'];
    echo "\n⚠️  Noch {$missing} Matches ohne Phases (könnten API-Fehler sein)\n";
}

$mysqli->close();
