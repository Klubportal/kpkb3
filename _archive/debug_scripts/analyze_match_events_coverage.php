<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🔍 MATCH EVENTS COVERAGE ANALYSE\n";
echo str_repeat("=", 60) . "\n\n";

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

// Statistik
$statsQuery = "
    SELECT
        COUNT(DISTINCT m.match_fifa_id) as total_played,
        COUNT(DISTINCT me.match_fifa_id) as with_events
    FROM comet_matches m
    LEFT JOIN comet_match_events me ON m.match_fifa_id = me.match_fifa_id
    WHERE m.competition_fifa_id IN ({$compIdsList})
      AND m.match_status = 'played'
";
$stats = $mysqli->query($statsQuery)->fetch_assoc();

echo "📊 MATCH EVENTS STATUS:\n";
echo "   Gespielte Matches total: {$stats['total_played']}\n";
echo "   Matches mit Events: {$stats['with_events']}\n";
echo "   Coverage: " . round(($stats['with_events'] / max($stats['total_played'], 1)) * 100, 1) . "%\n";
echo "   Fehlend: " . ($stats['total_played'] - $stats['with_events']) . " Matches\n\n";

// Aktuelle Einträge
$currentCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_match_events")->fetch_assoc()['count'];
echo "📊 Aktuelle Match Events: {$currentCount}\n\n";

// Events nach Typ
$eventsQuery = "
    SELECT
        event_type,
        COUNT(*) as count
    FROM comet_match_events
    GROUP BY event_type
    ORDER BY count DESC
";
$eventsResult = $mysqli->query($eventsQuery);

echo "📈 EVENTS NACH TYP:\n";
echo str_repeat("-", 60) . "\n";
while ($row = $eventsResult->fetch_assoc()) {
    echo sprintf("%-20s: %5d\n", $row['event_type'], $row['count']);
}

if ($stats['with_events'] < $stats['total_played']) {
    echo "\n❌ Es fehlen Events für " . ($stats['total_played'] - $stats['with_events']) . " Matches!\n";
} else {
    echo "\n✅ Alle gespielten Matches haben Events!\n";
}

$mysqli->close();
