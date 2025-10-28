<?php

// SYNC MATCH PHASES - Alle Matches von NK Prigorje (Team 598)
echo "⏱️  SYNC MATCH PHASES - NK PRIGORJE (Team 598)\n";
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

// Hole NUR GESPIELTE Matches von NK Prigorje
$matchesQuery = "SELECT match_fifa_id, team_fifa_id_home, team_fifa_id_away, match_status
                 FROM comet_matches
                 WHERE (team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId})
                   AND match_status = 'played'
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
    COUNT(CASE WHEN match_status = 'scheduled' THEN 1 END) as scheduled
    FROM comet_matches
    WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}";
$stats = $mysqli->query($allMatchesQuery)->fetch_assoc();
echo "📊 Total Matches: {$stats['total']} (Gespielt: {$stats['played']}, Geplant: {$stats['scheduled']})\n\n";

// Lösche alte Match Phases
echo "🗑️  Lösche alte Match Phases...\n";
$deleteQuery = "DELETE FROM comet_match_phases
                WHERE match_fifa_id IN (
                    SELECT match_fifa_id FROM comet_matches
                    WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}
                )";
$mysqli->query($deleteQuery);
echo "✅ Alte Daten gelöscht\n\n";

$totalProcessed = 0;
$totalPhases = 0;
$totalInserted = 0;
$errors = 0;

// Sync Match Phases für jedes Match
foreach ($matches as $match) {
    $matchFifaId = $match['match_fifa_id'];
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
        continue;
    }

    $phases = json_decode($response, true);
    if (empty($phases)) {
        echo " ⚠️  Keine Phases\n";
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

        // Konvertiere zu UTC (API gibt lokale Zeit, wir speichern auch UTC)
        $startDateTimeUtc = $startDateTime;
        $endDateTimeUtc = $endDateTime;

        if (!$phase) {
            continue;
        }

        // Insert Phase
        $stmt = $mysqli->prepare("
            INSERT INTO comet_match_phases (
                match_fifa_id, phase, home_score, away_score,
                regular_time, stoppage_time, phase_length,
                start_date_time, end_date_time,
                start_date_time_utc, end_date_time_utc,
                last_synced_at, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                home_score = VALUES(home_score),
                away_score = VALUES(away_score),
                regular_time = VALUES(regular_time),
                stoppage_time = VALUES(stoppage_time),
                phase_length = VALUES(phase_length),
                start_date_time = VALUES(start_date_time),
                end_date_time = VALUES(end_date_time),
                start_date_time_utc = VALUES(start_date_time_utc),
                end_date_time_utc = VALUES(end_date_time_utc),
                last_synced_at = NOW(),
                updated_at = NOW()
        ");

        $stmt->bind_param(
            "isiiiisssss",
            $matchFifaId, $phase, $homeScore, $awayScore,
            $regularTime, $stoppageTime, $phaseLength,
            $startDateTime, $endDateTime,
            $startDateTimeUtc, $endDateTimeUtc
        );

        if ($stmt->execute()) {
            $phasesInMatch++;
            $totalInserted++;
        } else {
            $errors++;
        }

        $stmt->close();
    }

    echo " ✅ {$phasesInMatch} Phase(s)\n";
    $totalPhases += $phasesInMatch;
    $totalProcessed++;

    // Rate limiting
    usleep(100000); // 100ms pause
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ MATCH PHASES SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches verarbeitet: {$totalProcessed}\n";
echo "   - Phases eingefügt: {$totalInserted}\n";
echo "   - Fehler: {$errors}\n\n";

// Zeige Statistik nach Phase-Typ
echo "📈 PHASES NACH TYP:\n";
echo str_repeat("-", 60) . "\n";

$statsQuery = "
    SELECT phase, COUNT(*) as count,
           AVG(phase_length) as avg_length,
           SUM(home_score) as total_home_goals,
           SUM(away_score) as total_away_goals
    FROM comet_match_phases
    WHERE match_fifa_id IN (
        SELECT match_fifa_id FROM comet_matches
        WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}
    )
    GROUP BY phase
    ORDER BY phase
";

$result = $mysqli->query($statsQuery);

while ($row = $result->fetch_assoc()) {
    echo sprintf("%-15s: %3d Phases | Ø %2d min | Goals: %3d:%3d\n",
        $row['phase'],
        $row['count'],
        round($row['avg_length']),
        $row['total_home_goals'],
        $row['total_away_goals']
    );
}

// Zeige Beispiele
echo "\n📋 BEISPIEL PHASES (letzte 5 Matches):\n";
echo str_repeat("-", 60) . "\n";

$examplesQuery = "
    SELECT mp.match_fifa_id, mp.phase, mp.home_score, mp.away_score,
           mp.phase_length, mp.start_date_time
    FROM comet_match_phases mp
    WHERE mp.match_fifa_id IN (
        SELECT match_fifa_id FROM comet_matches
        WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}
    )
    ORDER BY mp.match_fifa_id DESC,
             CASE mp.phase
                 WHEN 'FIRST_HALF' THEN 1
                 WHEN 'SECOND_HALF' THEN 2
                 WHEN 'FIRST_ET' THEN 3
                 WHEN 'SECOND_ET' THEN 4
                 WHEN 'PEN' THEN 5
             END
    LIMIT 10
";

$result = $mysqli->query($examplesQuery);

while ($row = $result->fetch_assoc()) {
    echo sprintf("Match %d | %-12s | %d:%d | %2d min | %s\n",
        $row['match_fifa_id'],
        $row['phase'],
        $row['home_score'],
        $row['away_score'],
        $row['phase_length'],
        $row['start_date_time'] ?? 'N/A'
    );
}

$mysqli->close();
