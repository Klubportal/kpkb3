<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=tenant_nknapijed', 'root', '');

echo "Spalten in comet_matches (tenant_nknapijed):\n";
echo "============================================\n\n";

$stmt = $pdo->query('DESCRIBE comet_matches');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo str_pad($row['Field'], 30) . " " . $row['Type'] . "\n";
}
