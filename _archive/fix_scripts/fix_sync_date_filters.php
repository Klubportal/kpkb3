<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kpkb3';

$conn = new mysqli($host, $username, $password, $database);

echo "🔄 Korrektur: -7 bis +14 Tage Filter für rankings, top_scorers, players...\n";
echo "============================================================\n\n";

// Update rankings
$sql1 = "UPDATE sync_schedules
         SET sync_params = '" . json_encode([
             'date_from' => '-7 days',
             'date_to' => '+14 days',
             'update_mode' => 'upsert'
         ]) . "'
         WHERE sync_type = 'rankings'";

if ($conn->query($sql1)) {
    echo "✅ rankings - Filter hinzugefügt (-7 bis +14 Tage)\n";
}

// Update top_scorers
$sql2 = "UPDATE sync_schedules
         SET sync_params = '" . json_encode([
             'date_from' => '-7 days',
             'date_to' => '+14 days',
             'update_mode' => 'upsert'
         ]) . "'
         WHERE sync_type = 'top_scorers'";

if ($conn->query($sql2)) {
    echo "✅ top_scorers - Filter hinzugefügt (-7 bis +14 Tage)\n";
}

echo "\n============================================================\n";
echo "✅ Korrektur abgeschlossen!\n\n";

// Zeige finale Übersicht
echo "📋 FINALE SYNC-ZEITPLÄNE (alle 5 Minuten, 9:00-23:00):\n";
echo "============================================================\n";

$result = $conn->query("
    SELECT sync_type, sync_params
    FROM sync_schedules
    WHERE frequency = 'custom' AND is_active = 1
    ORDER BY sync_type
");

while ($row = $result->fetch_assoc()) {
    $p = json_decode($row['sync_params'], true);
    $range = isset($p['date_from']) ? " ✅ [{$p['date_from']} bis {$p['date_to']}]" : " ⚠️ KEIN Filter";
    echo sprintf("  %-25s%s\n", $row['sync_type'], $range);
}

echo "\n💡 ALLE 7 Sync-Typen verwenden jetzt:\n";
echo "   - Zeitraum: -7 Tage bis +14 Tage (vom aktuellen Tag)\n";
echo "   - Frequenz: Alle 5 Minuten zwischen 9:00-23:00 Uhr\n";
echo "   - Update-Modus: UPSERT (INSERT oder UPDATE)\n";

$conn->close();
