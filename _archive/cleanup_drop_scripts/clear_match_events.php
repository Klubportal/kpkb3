<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');

echo "🗑️  Lösche alte match_events...\n";
$pdo->query("DELETE FROM match_events");

$result = $pdo->query("SELECT COUNT(*) as total FROM match_events");
$count = $result->fetch(PDO::FETCH_ASSOC)['total'];

echo "✅ Verbleibende Events: " . $count . "\n";
?>
