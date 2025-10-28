<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
$columns = $mysqli->query('SHOW COLUMNS FROM comet_top_scorers')->fetch_all(MYSQLI_ASSOC);
echo "Columns in comet_top_scorers:\n";
foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}

?>
