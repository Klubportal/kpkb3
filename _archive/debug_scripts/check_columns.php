<?php
$pdo = new PDO('mysql:host=localhost;dbname=tenant_nkprigorjem', 'root', '');

echo "Spalten in comet_top_scorers:\n";
$cols = $pdo->query('DESCRIBE comet_top_scorers')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}

// Beispieldaten
echo "\nBeispiel Daten:\n";
$data = $pdo->query('SELECT * FROM comet_top_scorers LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
print_r($data);

