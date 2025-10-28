<?php

$pdo = new PDO('mysql:host=localhost;dbname=kpkb3', 'root', '');
$columns = $pdo->query('SHOW COLUMNS FROM sync_schedules')->fetchAll(PDO::FETCH_ASSOC);
echo "Columns in sync_schedules:\n";
print_r($columns);

?>
