<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🔍 ANALYSE LEERER COMET TABELLEN\n";
echo str_repeat("=", 60) . "\n\n";

$tables = [
    'comet_match_team_officials',
    'comet_facility_fields',
    'comet_facilities',
    'comet_player_competition_stats'
];

foreach ($tables as $table) {
    echo "📋 TABELLE: {$table}\n";
    echo str_repeat("-", 60) . "\n";

    // Count
    $count = $mysqli->query("SELECT COUNT(*) as count FROM {$table}")->fetch_assoc()['count'];
    echo "   Einträge: {$count}\n";

    // Structure
    echo "   Struktur:\n";
    $result = $mysqli->query("DESCRIBE {$table}");
    while ($row = $result->fetch_assoc()) {
        echo "      - {$row['Field']} ({$row['Type']})\n";
    }
    echo "\n";
}

echo "\n💡 ANALYSE:\n";
echo str_repeat("=", 60) . "\n\n";

echo "1️⃣  comet_match_team_officials:\n";
echo "   → Trainer/Betreuer PRO MATCH (nicht generell)\n";
echo "   → Endpoint: /match/{matchId}/teamOfficials\n";
echo "   → Haben wir bereits in comet_coaches (generell)\n";
echo "   → Kann gefüllt werden für Match-spezifische Daten\n\n";

echo "2️⃣  comet_facilities + comet_facility_fields:\n";
echo "   → Stadien/Spielstätten und Spielfelder\n";
echo "   → Endpoint vermutlich: /facility/{facilityId}\n";
echo "   → Müssen wir testen ob verfügbar\n\n";

echo "3️⃣  comet_player_competition_stats:\n";
echo "   → Spieler-Statistiken PRO WETTBEWERB\n";
echo "   → Endpoint: /competition/{compId}/{teamId}/players\n";
echo "   → Detailliertere Stats als in comet_players\n";
echo "   → Müssen wir testen ob verfügbar\n\n";

$mysqli->close();
