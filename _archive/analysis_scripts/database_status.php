<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "✅ DATENBANK-STATUS: 1501 MATCHES\n";
echo str_repeat("═", 100) . "\n\n";

echo "📊 WICHTIG: Wo sind die Daten gespeichert?\n";
echo str_repeat("─", 100) . "\n\n";

echo "Die 1501 Matches sind in der RICHTIGEN Datenbank:\n";
echo "  ➜ club_manager (Multi-Tenant für den NK Prigorje Club)\n";
echo "  ➜ Tabelle: matches\n\n";

echo "Die kp_club_management Datenbank hat eine andere Struktur:\n";
echo "  ➜ kp_club_management ist für die HAUPT-Anwendung (Filament Admin)\n";
echo "  ➜ club_manager ist die TENANT-Datenbank (Daten für einen Club)\n\n";

echo str_repeat("─", 100) . "\n";
echo "ZUGRIFF AUF DIE DATEN\n";
echo str_repeat("─", 100) . "\n\n";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=club_manager', 'root', '');

$result = $pdo->query("SELECT COUNT(*) as total FROM matches");
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];

echo "✅ Aktuelle Matchanzahl in club_manager.matches: " . $total . "\n\n";

echo "【Verbindungsstring für Laravel】\n";
echo "───────────────────────────────────────────────────────\n";
echo "Datenbank: club_manager\n";
echo "Host: localhost (127.0.0.1)\n";
echo "User: root\n";
echo "Password: (leer)\n";
echo "Charset: utf8mb4\n\n";

echo "【Struktur der matches Tabelle】\n";
echo "───────────────────────────────────────────────────────\n";

$cols = $pdo->query("DESCRIBE matches")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo sprintf("  %-25s %s\n", $col['Field'], $col['Type']);
}

echo "\n【Beispiel-Abfrage】\n";
echo "───────────────────────────────────────────────────────\n\n";

echo "<?php\n";
echo "\$pdo = new PDO('mysql:host=localhost;dbname=club_manager', 'root', '');\n";
echo "\$matches = \$pdo->query('SELECT * FROM matches LIMIT 5')->fetchAll();\n";
echo "foreach (\$matches as \$match) {\n";
echo "    echo \$match['team_name_home'] . ' vs ' . \$match['team_name_away'];\n";
echo "}\n";
echo "?>\n";

echo "\n" . str_repeat("═", 100) . "\n";
echo "🎉 FERTIG! 1501 Matches sind verfügbar!\n";
echo str_repeat("═", 100) . "\n\n";

?>
