<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
$columns = $mysqli->query('SHOW COLUMNS FROM comet_club_competitions')->fetch_all(MYSQLI_ASSOC);
echo "Columns in comet_club_competitions:\n";
foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}

echo "\n\nSample competition:\n";
$sample = $mysqli->query('SELECT * FROM comet_club_competitions LIMIT 1')->fetch_assoc();
print_r($sample);

?>
