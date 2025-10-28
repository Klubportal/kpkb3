<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');

echo "📋 Alle Tabellen in kp_club_management:\n";
echo str_repeat("─", 80) . "\n\n";

$result = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kp_club_management' ORDER BY TABLE_NAME");

$tables = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $tables[] = $row['TABLE_NAME'];
    echo "  • " . $row['TABLE_NAME'] . "\n";
}

echo "\n" . str_repeat("─", 80) . "\n";
echo "Suche nach Paket/Subscription Tabellen...\n\n";

$subscription_tables = array_filter($tables, function($t) {
    return stripos($t, 'paket') !== false || stripos($t, 'subscription') !== false || stripos($t, 'plan') !== false;
});

if (!empty($subscription_tables)) {
    echo "✅ Gefunden:\n";
    foreach ($subscription_tables as $t) {
        echo "   • " . $t . "\n";
    }
} else {
    echo "❌ Keine Paket/Subscription-Tabellen gefunden\n";
}

?>
