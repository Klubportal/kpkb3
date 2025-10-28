<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "📊 COMET DATA INVENTORY - WAS HABEN WIR BEREITS?\n";
echo str_repeat("=", 60) . "\n\n";

// Check existing tables
$tables = [
    'comet_competitions' => 'Wettbewerbe',
    'comet_matches' => 'Spiele',
    'comet_match_phases' => 'Spielphasen (Halbzeiten)',
    'comet_match_events' => 'Spielereignisse (Tore, Karten, etc.)',
    'comet_match_players' => 'Spieler pro Match (Aufstellung)',
    'comet_players' => 'Spieler (Stammdaten)',
    'comet_coaches' => 'Trainer',
    'comet_club_representatives' => 'Vereinsfunktionäre',
    'comet_rankings' => 'Tabellen',
    'comet_top_scorers' => 'Torschützenliste'
];

echo "✅ VORHANDENE DATEN:\n";
echo str_repeat("-", 60) . "\n";
foreach ($tables as $table => $description) {
    $result = $mysqli->query("SELECT COUNT(*) as count FROM {$table}");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo sprintf("%-30s: %6d Einträge - %s\n", $table, $count, $description);
    }
}

echo "\n\n❓ WAS KÖNNTE NOCH NÜTZLICH SEIN?\n";
echo str_repeat("=", 60) . "\n\n";

echo "🏟️  STAMMDATEN:\n";
echo "   1. Teams/Vereine Details\n";
echo "      - Vereinsinfos, Logos, Stadien, Gründungsjahr\n";
echo "      - Endpoint: /team/{teamFifaId}\n\n";

echo "👥 PERSONEN:\n";
echo "   2. Schiedsrichter\n";
echo "      - Namen, Fotos, Statistiken\n";
echo "      - Endpoint: /match/{matchId}/officials (haben wir teilweise)\n\n";

echo "📊 STATISTIKEN:\n";
echo "   3. Team-Statistiken pro Competition\n";
echo "      - Ballbesitz, Schüsse, Passquote, etc.\n";
echo "      - Endpoint: /competition/{compId}/{teamId}/statistics\n\n";

echo "   4. Player-Statistiken pro Competition\n";
echo "      - Detaillierte Spielerstatistiken\n";
echo "      - Endpoint: /competition/{compId}/{teamId}/players\n\n";

echo "   5. Match-Statistiken\n";
echo "      - Ballbesitz, Schüsse aufs Tor, Ecken, etc.\n";
echo "      - Endpoint: /match/{matchId}/statistics\n\n";

echo "🏆 WETTBEWERBS-DETAILS:\n";
echo "   6. Competition Gruppen/Runden\n";
echo "      - Gruppenphase, KO-Runde, etc.\n";
echo "      - Endpoint: /competition/{compId}/groups oder /rounds\n\n";

echo "📅 SPIELPLAN:\n";
echo "   7. Match-Timeline/Spielminuten\n";
echo "      - Genaue Zeiten der Events\n";
echo "      - Haben wir schon in match_events (event_time)\n\n";

echo "🎯 EMPFEHLUNG - AM NÜTZLICHSTEN:\n";
echo str_repeat("=", 60) . "\n";
echo "1️⃣  MATCH STATISTICS (Ballbesitz, Schüsse, Ecken)\n";
echo "   → Sehr interessant für Spielberichte!\n\n";
echo "2️⃣  TEAM DETAILS (Logos, Stadien, Vereinsinfos)\n";
echo "   → Wichtig für Vereinsprofile!\n\n";
echo "3️⃣  PLAYER STATISTICS PRO COMPETITION\n";
echo "   → Detailliertere Spielerstatistiken!\n\n";
echo "4️⃣  SCHIEDSRICHTER DETAILS\n";
echo "   → Vollständige Schiedsrichterdaten!\n\n";

$mysqli->close();
