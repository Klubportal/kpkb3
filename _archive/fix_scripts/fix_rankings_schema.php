<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');

echo "📋 Aktuelle rankings Tabelle Schema:\n";
echo str_repeat("─", 80) . "\n\n";

$result = $pdo->query("DESCRIBE rankings");

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("  %25s | %20s | Null: %3s | Key: %3s\n",
        $row['Field'],
        $row['Type'],
        $row['Null'],
        $row['Key']
    );
}

echo "\n" . str_repeat("─", 80) . "\n";
echo "🔧 Modifiziere Tabelle...\n\n";

// Make club_id nullable
$pdo->query("ALTER TABLE rankings MODIFY club_id BIGINT UNSIGNED NULL");

echo "✅ club_id ist jetzt NULL erlaubt\n";
?>
