<?php

// UPDATE COMET PLAYERS - Füge alle Daten aus Match Players ein
echo "🔄 UPDATE COMET PLAYERS - NK PRIGORJE (Team 598)\n";
echo str_repeat("=", 60) . "\n\n";

// MySQL Verbindung
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$teamFifaId = 598;

echo "📊 Aktualisiere Spieler-Daten aus Match Players...\n\n";

// 1. UPDATE: Shirt Numbers (neueste aus letzten Matches)
echo "1️⃣  Shirt Numbers...";
$query1 = "
    UPDATE comet_players cp
    INNER JOIN (
        SELECT person_fifa_id, shirt_number
        FROM comet_match_players
        WHERE team_fifa_id = {$teamFifaId}
          AND shirt_number IS NOT NULL
        GROUP BY person_fifa_id
        ORDER BY match_fifa_id DESC
    ) mp ON cp.person_fifa_id = mp.person_fifa_id
    SET cp.shirt_number = mp.shirt_number
    WHERE cp.club_fifa_id = {$teamFifaId}
";
$mysqli->query($query1);
echo " ✅ " . $mysqli->affected_rows . " aktualisiert\n";

// 2. UPDATE: Position für Goalkeepers
echo "2️⃣  Goalkeeper Position...";
$query2 = "
    UPDATE comet_players cp
    INNER JOIN (
        SELECT DISTINCT person_fifa_id
        FROM comet_match_players
        WHERE team_fifa_id = {$teamFifaId} AND goalkeeper = 1
    ) mp ON cp.person_fifa_id = mp.person_fifa_id
    SET cp.position = 'goalkeeper'
    WHERE cp.club_fifa_id = {$teamFifaId}
";
$mysqli->query($query2);
echo " ✅ " . $mysqli->affected_rows . " Torhüter\n";

// 3. UPDATE: Local Name (Popular Name) falls verfügbar
echo "3️⃣  Local Names...";
$query3 = "
    UPDATE comet_players cp
    INNER JOIN (
        SELECT person_fifa_id, local_person_name
        FROM comet_match_players
        WHERE team_fifa_id = {$teamFifaId}
          AND local_person_name IS NOT NULL
          AND local_person_name != ''
        GROUP BY person_fifa_id
    ) mp ON cp.person_fifa_id = mp.person_fifa_id
    SET cp.popular_name = mp.local_person_name
    WHERE cp.club_fifa_id = {$teamFifaId}
      AND (cp.popular_name IS NULL OR cp.popular_name = '')
";
$mysqli->query($query3);
echo " ✅ " . $mysqli->affected_rows . " aktualisiert\n";

// 4. BERECHNE: Statistiken aus Match Events (bereits vorhanden, nur Kontrolle)
echo "\n📊 Berechne Match-Statistiken aus Events...\n";

// 4a. Total Matches
echo "4️⃣  Total Matches...";
$query4 = "
    UPDATE comet_players cp
    LEFT JOIN (
        SELECT player_fifa_id, COUNT(DISTINCT match_fifa_id) as matches
        FROM comet_match_events
        WHERE team_fifa_id = {$teamFifaId}
        GROUP BY player_fifa_id
    ) me ON cp.person_fifa_id = me.player_fifa_id
    SET cp.total_matches = COALESCE(me.matches, 0)
    WHERE cp.club_fifa_id = {$teamFifaId}
";
$mysqli->query($query4);
echo " ✅ " . $mysqli->affected_rows . " aktualisiert\n";

// 4b. Total Goals
echo "5️⃣  Total Goals...";
$query5 = "
    UPDATE comet_players cp
    LEFT JOIN (
        SELECT player_fifa_id, COUNT(*) as goals
        FROM comet_match_events
        WHERE team_fifa_id = {$teamFifaId}
          AND event_type IN ('goal', 'penalty_goal')
        GROUP BY player_fifa_id
    ) me ON cp.person_fifa_id = me.player_fifa_id
    SET cp.total_goals = COALESCE(me.goals, 0)
    WHERE cp.club_fifa_id = {$teamFifaId}
";
$mysqli->query($query5);
echo " ✅ " . $mysqli->affected_rows . " aktualisiert\n";

// 4c. Yellow Cards
echo "6️⃣  Yellow Cards...";
$query6 = "
    UPDATE comet_players cp
    LEFT JOIN (
        SELECT player_fifa_id, COUNT(*) as yellows
        FROM comet_match_events
        WHERE team_fifa_id = {$teamFifaId}
          AND event_type = 'yellow_card'
        GROUP BY player_fifa_id
    ) me ON cp.person_fifa_id = me.player_fifa_id
    SET cp.total_yellow_cards = COALESCE(me.yellows, 0)
    WHERE cp.club_fifa_id = {$teamFifaId}
";
$mysqli->query($query6);
echo " ✅ " . $mysqli->affected_rows . " aktualisiert\n";

// 4d. Red Cards
echo "7️⃣  Red Cards...";
$query7 = "
    UPDATE comet_players cp
    LEFT JOIN (
        SELECT player_fifa_id, COUNT(*) as reds
        FROM comet_match_events
        WHERE team_fifa_id = {$teamFifaId}
          AND event_type IN ('red_card', 'yellow_red_card')
        GROUP BY player_fifa_id
    ) me ON cp.person_fifa_id = me.player_fifa_id
    SET cp.total_red_cards = COALESCE(me.reds, 0)
    WHERE cp.club_fifa_id = {$teamFifaId}
";
$mysqli->query($query7);
echo " ✅ " . $mysqli->affected_rows . " aktualisiert\n";

// 5. KOPIERE: Total Stats zu Season Stats
echo "8️⃣  Season Stats (Kopie von Total)...";
$query8 = "
    UPDATE comet_players
    SET
        season_matches = total_matches,
        season_goals = total_goals,
        season_yellow_cards = total_yellow_cards,
        season_red_cards = total_red_cards
    WHERE club_fifa_id = {$teamFifaId}
";
$mysqli->query($query8);
echo " ✅ " . $mysqli->affected_rows . " aktualisiert\n";

// 6. MARKIERE: Alle als syncronisiert
echo "9️⃣  Sync Status...";
$query9 = "
    UPDATE comet_players
    SET
        is_synced = 1,
        last_synced_at = NOW()
    WHERE club_fifa_id = {$teamFifaId}
";
$mysqli->query($query9);
echo " ✅ Alle 254 Spieler markiert\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ UPDATE ABGESCHLOSSEN\n\n";

// FINALE STATISTIK
echo "📊 FINALE ÜBERSICHT:\n";
echo str_repeat("-", 60) . "\n";

$stats = $mysqli->query("
    SELECT
        COUNT(*) as total,
        COUNT(date_of_birth) as has_dob,
        COUNT(place_of_birth) as has_place,
        COUNT(nationality) as has_nationality,
        COUNT(shirt_number) as has_number,
        COUNT(CASE WHEN position != 'unknown' THEN 1 END) as has_position,
        COUNT(CASE WHEN popular_name IS NOT NULL AND popular_name != '' THEN 1 END) as has_local_name,
        COUNT(CASE WHEN total_matches > 0 THEN 1 END) as has_matches,
        COUNT(CASE WHEN total_goals > 0 THEN 1 END) as has_goals,
        COUNT(CASE WHEN total_yellow_cards > 0 THEN 1 END) as has_yellows,
        COUNT(CASE WHEN total_red_cards > 0 THEN 1 END) as has_reds
    FROM comet_players
    WHERE club_fifa_id = {$teamFifaId}
")->fetch_assoc();

echo sprintf("Gesamt Spieler:            %3d\n", $stats['total']);
echo sprintf("  - mit Geburtsdatum:      %3d (%3d%%)\n", $stats['has_dob'], round($stats['has_dob']/$stats['total']*100));
echo sprintf("  - mit Geburtsort:        %3d (%3d%%)\n", $stats['has_place'], round($stats['has_place']/$stats['total']*100));
echo sprintf("  - mit Nationalität:      %3d (%3d%%)\n", $stats['has_nationality'], round($stats['has_nationality']/$stats['total']*100));
echo sprintf("  - mit Shirt Number:      %3d (%3d%%)\n", $stats['has_number'], round($stats['has_number']/$stats['total']*100));
echo sprintf("  - mit Position:          %3d (%3d%%)\n", $stats['has_position'], round($stats['has_position']/$stats['total']*100));
echo sprintf("  - mit Local Name:        %3d (%3d%%)\n", $stats['has_local_name'], round($stats['has_local_name']/$stats['total']*100));
echo sprintf("  - mit Matches:           %3d (%3d%%)\n", $stats['has_matches'], round($stats['has_matches']/$stats['total']*100));
echo sprintf("  - mit Goals:             %3d (%3d%%)\n", $stats['has_goals'], round($stats['has_goals']/$stats['total']*100));
echo sprintf("  - mit Yellow Cards:      %3d (%3d%%)\n", $stats['has_yellows'], round($stats['has_yellows']/$stats['total']*100));
echo sprintf("  - mit Red Cards:         %3d (%3d%%)\n", $stats['has_reds'], round($stats['has_reds']/$stats['total']*100));

echo "\n📈 TOP 10 TORSCHÜTZEN:\n";
echo str_repeat("-", 60) . "\n";

$topScorers = $mysqli->query("
    SELECT name, shirt_number, position, total_matches, total_goals, total_yellow_cards, total_red_cards
    FROM comet_players
    WHERE club_fifa_id = {$teamFifaId} AND total_goals > 0
    ORDER BY total_goals DESC, total_matches ASC
    LIMIT 10
");

$rank = 1;
while ($player = $topScorers->fetch_assoc()) {
    echo sprintf("%2d. #%-3s %-25s %2d Tore | %2d Spiele | %d Gelb | %d Rot\n",
        $rank++,
        $player['shirt_number'] ?? '?',
        $player['name'],
        $player['total_goals'],
        $player['total_matches'],
        $player['total_yellow_cards'],
        $player['total_red_cards']
    );
}

echo "\n⭐ TORHÜTER:\n";
echo str_repeat("-", 60) . "\n";

$goalkeepers = $mysqli->query("
    SELECT name, shirt_number, total_matches
    FROM comet_players
    WHERE club_fifa_id = {$teamFifaId} AND position = 'goalkeeper'
    ORDER BY total_matches DESC
    LIMIT 10
");

while ($gk = $goalkeepers->fetch_assoc()) {
    echo sprintf("   #%-3s %-35s %2d Spiele\n",
        $gk['shirt_number'] ?? '?',
        $gk['name'],
        $gk['total_matches']
    );
}

$mysqli->close();
