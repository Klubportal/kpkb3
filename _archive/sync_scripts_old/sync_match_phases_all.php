<?php

// SYNC MATCH PHASES - ALLE GESPIELTEN MATCHES
echo "⏱️  SYNC MATCH PHASES - ALLE GESPIELTEN MATCHES\n";
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

// Hole ALLE GESPIELTEN Matches (nicht nur NK Prigorje)
$matchesQuery = "SELECT match_fifa_id, team_fifa_id_home, team_fifa_id_away, match_status
                 FROM comet_matches
                 WHERE match_status = 'played'
                 ORDER BY match_fifa_id DESC";
$matchesResult = $mysqli->query($matchesQuery);

if (!$matchesResult) {
    die("Query Error: " . $mysqli->error . "\n");
}

$matches = [];
while ($row = $matchesResult->fetch_assoc()) {
    $matches[] = $row;
}

echo "📊 Gespielte Matches gefunden: " . count($matches) . "\n";

// Zeige auch Statistik aller Matches
$allMatchesQuery = "SELECT
    COUNT(*) as total,
    COUNT(CASE WHEN match_status = 'played' THEN 1 END) as played,
    COUNT(CASE WHEN match_status = 'scheduled' THEN 1 END) as scheduled,
    COUNT(CASE WHEN match_status = 'postponed' THEN 1 END) as postponed
    FROM comet_matches";
$stats = $mysqli->query($allMatchesQuery)->fetch_assoc();
echo "📊 Total Matches: {$stats['total']} (Gespielt: {$stats['played']}, Geplant: {$stats['scheduled']}, Verschoben: {$stats['postponed']})\n\n";

// Lösche alte Match Phases
echo "🗑️  Lösche alte Match Phases...\n";
$deleteQuery = "TRUNCATE TABLE comet_match_phases";
$mysqli->query($deleteQuery);
echo "✅ Alte Daten gelöscht\n\n";

$totalProcessed = 0;
$totalPhases = 0;
$totalInserted = 0;
$errors = 0;

// Sync Match Phases für jedes Match
foreach ($matches as $match) {
    $matchFifaId = $match['match_fifa_id'];

    // Progress indicator every 50 matches
    if ($totalProcessed % 50 == 0 && $totalProcessed > 0) {
        echo "\n📊 Progress: {$totalProcessed}/" . count($matches) . " matches processed...\n\n";
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
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ MATCH PHASES SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Phases eingefügt: {$totalInserted}\n";
echo "   - Fehler: {$errors}\n\n";

// Zeige Phase-Statistik
$statsQuery = "SELECT
    phase,
    COUNT(*) as count,
    ROUND(AVG(phase_length), 0) as avg_length,
    ROUND(AVG(home_score), 1) as avg_home,
    ROUND(AVG(away_score), 1) as avg_away
    FROM comet_match_phases
    GROUP BY phase
    ORDER BY phase";
$statsResult = $mysqli->query($statsQuery);

echo "📈 PHASES NACH TYP:\n";
echo str_repeat("-", 60) . "\n";
while ($stat = $statsResult->fetch_assoc()) {
    $phase = str_pad($stat['phase'], 15);
    $count = str_pad($stat['count'], 5, ' ', STR_PAD_LEFT);
    $avgLength = str_pad($stat['avg_length'], 2, ' ', STR_PAD_LEFT);
    $goals = str_pad($stat['avg_home'], 3, ' ', STR_PAD_LEFT) . ":" . str_pad($stat['avg_away'], 3);
    echo "{$phase}: {$count} Phases | Ø {$avgLength} min | Avg Goals: {$goals}\n";
}

// Zeige Beispiel Phases
echo "\n📋 BEISPIEL PHASES (letzte 5 Matches):\n";
echo str_repeat("-", 60) . "\n";
$examplesQuery = "SELECT
    match_fifa_id,
    phase,
    home_score,
    away_score,
    phase_length,
    start_date_time
    FROM comet_match_phases
    ORDER BY id DESC
    LIMIT 10";
$examplesResult = $mysqli->query($examplesQuery);
while ($example = $examplesResult->fetch_assoc()) {
    $matchId = str_pad($example['match_fifa_id'], 11);
    $phase = str_pad($example['phase'], 15);
    $score = str_pad($example['home_score'] . ":" . $example['away_score'], 5);
    $length = str_pad($example['phase_length'] . " min", 7);
    $startTime = $example['start_date_time'] ?? 'N/A';
    echo "Match {$matchId} | {$phase} | {$score} | {$length} | {$startTime}\n";
}

$mysqli->close();
