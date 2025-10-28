<?php

echo "🔍 NK PRIGORJE DATA IN LANDLORD DATABASE\n";
echo str_repeat("=", 80) . "\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$prigorjeFifaId = 598; // NK Prigorje Markuševec

// Check matches
echo "📊 MATCHES (as Home or Away):\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
");
$row = $result->fetch_assoc();
echo "   Total matches: {$row['count']}\n";

$result = $mysqli->query("
    SELECT match_fifa_id, date_time_local, team_name_home, team_name_away,
           team_score_home, team_score_away, match_status
    FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
    ORDER BY date_time_local DESC
    LIMIT 5
");
echo "\n   Recent matches:\n";
while ($row = $result->fetch_assoc()) {
    $date = $row['date_time_local'] ? date('Y-m-d', strtotime($row['date_time_local'])) : 'N/A';
    echo "   - {$date}: {$row['team_name_home']} {$row['team_score_home']}-{$row['team_score_away']} {$row['team_name_away']} ({$row['match_status']})\n";
}

// Check rankings
echo "\n📊 RANKINGS:\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("
    SELECT COUNT(*) as count
    FROM comet_rankings
    WHERE team_fifa_id = $prigorjeFifaId
");
$row = $result->fetch_assoc();
echo "   Total rankings: {$row['count']}\n";

$result = $mysqli->query("
    SELECT international_competition_name, position, points, matches_played
    FROM comet_rankings
    WHERE team_fifa_id = $prigorjeFifaId
");
while ($row = $result->fetch_assoc()) {
    echo "   - {$row['international_competition_name']}: Position {$row['position']} ({$row['points']} pts, {$row['matches_played']} games)\n";
}

// Check top scorers
echo "\n📊 TOP SCORERS (Players from NK Prigorje):\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("
    SELECT COUNT(*) as count
    FROM comet_top_scorers
    WHERE club_id = $prigorjeFifaId
");
$row = $result->fetch_assoc();
echo "   Total scorers: {$row['count']}\n";

$result = $mysqli->query("
    SELECT international_first_name, international_last_name, goals,
           international_competition_name
    FROM comet_top_scorers
    WHERE club_id = $prigorjeFifaId
    ORDER BY goals DESC
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    echo "   - {$row['international_first_name']} {$row['international_last_name']}: {$row['goals']} goals ({$row['international_competition_name']})\n";
}

$mysqli->close();
