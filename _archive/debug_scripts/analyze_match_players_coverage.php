<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🔍 MATCH PLAYERS ANALYSE\n";
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
        COUNT(DISTINCT mp.match_fifa_id) as with_players
    FROM comet_matches m
    LEFT JOIN comet_match_players mp ON m.match_fifa_id = mp.match_fifa_id
    WHERE m.competition_fifa_id IN ({$compIdsList})
      AND m.match_status = 'played'
";
$stats = $mysqli->query($statsQuery)->fetch_assoc();

echo "📊 MATCH PLAYERS STATUS:\n";
echo "   Gespielte Matches total: {$stats['total_played']}\n";
echo "   Matches mit Players: {$stats['with_players']}\n";
echo "   Coverage: " . round(($stats['with_players'] / max($stats['total_played'], 1)) * 100, 1) . "%\n";
echo "   Fehlend: " . ($stats['total_played'] - $stats['with_players']) . " Matches\n\n";

// Aktuelle Einträge
$currentCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_match_players")->fetch_assoc()['count'];
echo "📊 Aktuelle Match Player Einträge: {$currentCount}\n";
echo "📊 Erwartet (bei ~22 Spieler pro Match): ca. " . ($stats['total_played'] * 22) . " Einträge\n\n";

if ($stats['with_players'] < $stats['total_played']) {
    echo "❌ Es fehlen Daten für " . ($stats['total_played'] - $stats['with_players']) . " Matches!\n";
    echo "💡 Soll ich die fehlenden Match Players syncen?\n";
} else {
    echo "✅ Alle gespielten Matches haben Player-Daten!\n";
}

$mysqli->close();
