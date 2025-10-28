<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "📋 ÜBERSICHT: ERFOLGREICH GENUTZTE ENDPOINTS\n";
echo str_repeat("═", 100) . "\n\n";

$apiBase = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Diese Endpoints wissen wir funktionieren
$workingEndpoints = [
    "/api/export/comet/competitions?active=true&teamFifaId=598" => [
        "name" => "Alle aktiven Wettbewerbe für Club 598",
        "result" => "✅ 11 Wettbewerbe abgerufen"
    ],
    "/api/export/comet/competition/{id}/matches" => [
        "name" => "Alle Matches eines Wettbewerbs",
        "result" => "✅ 1501 Matches abgerufen"
    ],
    "/api/export/comet/competition/{id}/ranking" => [
        "name" => "Rankings/Tabelle eines Wettbewerbs",
        "result" => "✅ 138 Ranking-Einträge abgerufen"
    ],
    "/api/export/comet/match/{id}/events" => [
        "name" => "Match Events (Tore, Karten, Auswechslungen)",
        "result" => "✅ ~1238 Events abgerufen"
    ],
];

echo "【VERFÜGBARE DATENQUELLEN】\n\n";

foreach ($workingEndpoints as $endpoint => $info) {
    echo sprintf("📌 %s\n", $info['name']);
    echo sprintf("   Endpoint: %s\n", $endpoint);
    echo sprintf("   Status: %s\n\n", $info['result']);
}

echo str_repeat("═", 100) . "\n";
echo "【AKTUELLE DATENBANK STATUS】\n";
echo str_repeat("═", 100) . "\n\n";

$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');

// Check each table
$tables = ['competitions', 'matches', 'rankings', 'match_events', 'players', 'player_statistics'];

foreach ($tables as $table) {
    try {
        $result = $pdo->query("SELECT COUNT(*) as cnt FROM " . $table);
        $count = $result->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo sprintf("📊 %-25s: %6d Einträge\n", $table, $count);
    } catch (Exception $e) {
        echo sprintf("📊 %-25s: ❌ Tabelle existiert nicht\n", $table);
    }
}

echo "\n" . str_repeat("═", 100) . "\n";
echo "【KOMMENDE SCHRITTE】\n";
echo str_repeat("═", 100) . "\n\n";

echo "Die API stellt diese Endpoints NICHT zur Verfügung (404):\n";
echo "  ❌ Spieler-Details / Player Rosters\n";
echo "  ❌ Spieler-Statistiken\n";
echo "  ❌ Team-Statistiken pro Match\n";
echo "  ❌ Match Aufstellungen\n";
echo "  ❌ Venues/Stadien\n";

echo "\nWas wir haben:\n";
echo "  ✅ 11 Wettbewerbe\n";
echo "  ✅ 1501 Matches mit allen Grunddaten\n";
echo "  ✅ 138 Ranking-Einträge (Tabellenplatzierungen)\n";
echo "  ✅ ~1238 Match Events (Tore, Karten, Auswechslungen)\n";

echo "\nMögliche nächste Schritte:\n";
echo "  1. REST API Dokumentation nochmal durchsuchen\n";
echo "  2. Fehlende Spieler-Daten aus Rankings auslesen\n";
echo "  3. Berechnete Spieler-Statistiken aus Events generieren\n";
echo "  4. Frontend Views mit verfügbaren Daten erstellen\n";

echo "\n" . str_repeat("═", 100) . "\n\n";

?>
