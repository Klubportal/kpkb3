<?php
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');

echo "📊 RANKINGS - Short Team Names:\n";
echo str_repeat("=", 80) . "\n";
$result = $mysqli->query('SELECT position, international_team_name, points FROM comet_rankings ORDER BY id LIMIT 10');
while($row = $result->fetch_assoc()) {
    echo "{$row['position']}. {$row['international_team_name']} ({$row['points']} pts)\n";
}

echo "\n📊 MATCHES - Short Team Names:\n";
echo str_repeat("=", 80) . "\n";
$result = $mysqli->query('SELECT team_name_home, team_name_away FROM comet_matches LIMIT 5');
while($row = $result->fetch_assoc()) {
    echo "{$row['team_name_home']} vs {$row['team_name_away']}\n";
}

$mysqli->close();
