<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');

$result = $pdo->query("SELECT COUNT(*) as total FROM match_events");
$count = $result->fetch(PDO::FETCH_ASSOC)['total'];

echo "📊 Aktuelle match_events: " . $count . "\n";

// Show by type
if ($count > 0) {
    $result = $pdo->query("
        SELECT event_type, COUNT(*) as cnt
        FROM match_events
        GROUP BY event_type
        ORDER BY cnt DESC
    ");

    echo "\nNach Typ:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  • " . $row['event_type'] . ": " . $row['cnt'] . "\n";
    }
}
?>
