<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
$match = $mysqli->query('SELECT * FROM comet_matches WHERE match_status = "played" LIMIT 1')->fetch_assoc();

echo "Sample match data:\n";
echo str_repeat("=", 60) . "\n";
foreach($match as $k => $v) {
    $value = $v === null ? 'NULL' : (strlen($v) > 50 ? substr($v, 0, 50) . '...' : $v);
    echo sprintf("%-30s: %s\n", $k, $value);
}

echo "\n\nNULL fields:\n";
echo str_repeat("=", 60) . "\n";
foreach($match as $k => $v) {
    if($v === null) {
        echo "  - $k\n";
    }
}

?>
