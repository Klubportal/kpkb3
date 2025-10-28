<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');

echo "🔧 Modifiziere match_events Tabelle...\n\n";

// Make team_fifa_id nullable
$pdo->query("ALTER TABLE match_events MODIFY team_fifa_id BIGINT NULL");
$pdo->query("ALTER TABLE match_events MODIFY player_fifa_id BIGINT NULL");
$pdo->query("ALTER TABLE match_events MODIFY player_fifa_id_2 BIGINT NULL");

echo "✅ team_fifa_id ist jetzt NULL erlaubt\n";
echo "✅ player_fifa_id ist jetzt NULL erlaubt\n";
echo "✅ player_fifa_id_2 ist jetzt NULL erlaubt\n";
?>
