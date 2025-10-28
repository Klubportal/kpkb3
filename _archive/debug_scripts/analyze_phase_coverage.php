<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🔍 MATCH PHASES ANALYSE\n";
echo str_repeat("=", 60) . "\n\n";

$teamFifaId = 598;

// 1. Welche Competitions hat NK Prigorje?
echo "📊 1. NK Prigorje Competitions:\n";
$competitionsQuery = "
    SELECT DISTINCT m.competition_fifa_id, m.international_competition_name
    FROM comet_matches m
    WHERE m.team_fifa_id_home = {$teamFifaId} OR m.team_fifa_id_away = {$teamFifaId}
    ORDER BY m.international_competition_name
";
$result = $mysqli->query($competitionsQuery);
$competitions = [];
while ($row = $result->fetch_assoc()) {
    $competitions[] = $row;
    echo "   - {$row['international_competition_name']} - FIFA ID: {$row['competition_fifa_id']}\n";
}
echo "\n";

// 2. Wie viele Matches gibt es in diesen Competitions?
echo "📊 2. Matches in NK Prigorje Competitions:\n";
foreach ($competitions as $comp) {
    $compId = $comp['competition_fifa_id'];
    $statsQuery = "
        SELECT
            COUNT(*) as total,
            COUNT(CASE WHEN match_status = 'played' THEN 1 END) as played,
            COUNT(CASE WHEN match_status = 'scheduled' THEN 1 END) as scheduled
        FROM comet_matches
        WHERE competition_fifa_id = {$compId}
    ";
    $stats = $mysqli->query($statsQuery)->fetch_assoc();
    echo "   {$comp['international_competition_name']}:\n";
    echo "      Total: {$stats['total']} | Gespielt: {$stats['played']} | Geplant: {$stats['scheduled']}\n";
}
echo "\n";

// 3. Gesamtstatistik
echo "📊 3. GESAMTSTATISTIK:\n";
$compIds = array_column($competitions, 'competition_fifa_id');
$compIdsList = implode(',', $compIds);

$totalStatsQuery = "
    SELECT
        COUNT(*) as total_matches,
        COUNT(CASE WHEN match_status = 'played' THEN 1 END) as played_matches,
        COUNT(CASE WHEN match_status = 'scheduled' THEN 1 END) as scheduled_matches
    FROM comet_matches
    WHERE competition_fifa_id IN ({$compIdsList})
";
$totalStats = $mysqli->query($totalStatsQuery)->fetch_assoc();

echo "   Matches in NK Prigorje Competitions:\n";
echo "      Total: {$totalStats['total_matches']}\n";
echo "      Gespielt: {$totalStats['played_matches']}\n";
echo "      Geplant: {$totalStats['scheduled_matches']}\n\n";

// 4. Wie viele Phases haben wir aktuell?
$phasesQuery = "SELECT COUNT(*) as count FROM comet_match_phases";
$currentPhases = $mysqli->query($phasesQuery)->fetch_assoc()['count'];

echo "📊 4. MATCH PHASES STATUS:\n";
echo "   Aktuell in DB: {$currentPhases} phases\n";
echo "   Erwartet (wenn alle gespielten Matches): " . ($totalStats['played_matches'] * 2) . " phases\n";
echo "   Fehlend: " . (($totalStats['played_matches'] * 2) - $currentPhases) . " phases\n\n";

// 5. Welche Matches haben Phases, welche nicht?
echo "📊 5. PHASE COVERAGE:\n";
$coverageQuery = "
    SELECT
        COUNT(DISTINCT m.match_fifa_id) as matches_with_phases
    FROM comet_matches m
    INNER JOIN comet_match_phases p ON m.match_fifa_id = p.match_fifa_id
    WHERE m.competition_fifa_id IN ({$compIdsList})
      AND m.match_status = 'played'
";
$matchesWithPhases = $mysqli->query($coverageQuery)->fetch_assoc()['matches_with_phases'];

echo "   Gespielte Matches mit Phases: {$matchesWithPhases} / {$totalStats['played_matches']}\n";
echo "   Coverage: " . round(($matchesWithPhases / max($totalStats['played_matches'], 1)) * 100, 1) . "%\n\n";

// 6. Nur NK Prigorje eigene Spiele?
$nkPrigorjeMatchesQuery = "
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE (team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId})
      AND match_status = 'played'
";
$nkPrigorjeMatches = $mysqli->query($nkPrigorjeMatchesQuery)->fetch_assoc()['count'];

$nkPrigorsePhasesQuery = "
    SELECT COUNT(DISTINCT p.match_fifa_id) as count
    FROM comet_match_phases p
    INNER JOIN comet_matches m ON p.match_fifa_id = m.match_fifa_id
    WHERE (m.team_fifa_id_home = {$teamFifaId} OR m.team_fifa_id_away = {$teamFifaId})
      AND m.match_status = 'played'
";
$nkPrigorjePhases = $mysqli->query($nkPrigorsePhasesQuery)->fetch_assoc()['count'];

echo "📊 6. NUR NK PRIGORJE EIGENE SPIELE:\n";
echo "   Gespielte Matches: {$nkPrigorjeMatches}\n";
echo "   Matches mit Phases: {$nkPrigorjePhases}\n";
echo "   Coverage: " . round(($nkPrigorjePhases / max($nkPrigorjeMatches, 1)) * 100, 1) . "%\n\n";

echo "🎯 FAZIT:\n";
if ($currentPhases == 76 && $nkPrigorjeMatches == 38) {
    echo "   ✅ Du hast nur NK Prigorje's EIGENE Spiele (38 Matches = 76 Phases)\n";
    echo "   ❌ FEHLT: Alle anderen Spiele in den Competitions ({$totalStats['played_matches']} - {$nkPrigorjeMatches} = " . ($totalStats['played_matches'] - $nkPrigorjeMatches) . " Matches)\n";
    echo "   📥 Sollte sein: {$totalStats['played_matches']} Matches = " . ($totalStats['played_matches'] * 2) . " Phases\n";
}

$mysqli->close();
