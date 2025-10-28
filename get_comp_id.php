<?php
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
$result = $mysqli->query('SELECT DISTINCT competition_fifa_id, team_fifa_id FROM comet_rankings LIMIT 1');
$row = $result->fetch_assoc();
echo "Competition ID: {$row['competition_fifa_id']}\n";
echo "Team ID: {$row['team_fifa_id']}\n";
