<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=tenant_nknapijed', 'root', '');
$stmt = $pdo->query('SHOW TABLES');

echo "Tabellen in tenant_nknapijed:\n";
echo "================================\n";

while($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}

echo "\n";

// Prüfe speziell nach template_settings
$check = $pdo->query("SHOW TABLES LIKE 'template_settings'");
if ($check->rowCount() > 0) {
    echo "✅ template_settings existiert\n";

    $count = $pdo->query("SELECT COUNT(*) FROM template_settings")->fetchColumn();
    echo "Anzahl Einträge: $count\n";
} else {
    echo "❌ template_settings existiert NICHT\n";
}
