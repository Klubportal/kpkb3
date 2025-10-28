<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "🔄 TRANSFERIERE 1501 MATCHES von club_manager → kp_club_management\n";
echo str_repeat("═", 100) . "\n\n";

try {
    // Connect to both databases
    $sourceDb = new PDO('mysql:host=127.0.0.1;dbname=club_manager', 'root', '');
    $targetDb = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');

    $sourceDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $targetDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "【1】 Quell- und Zieldatenbank verbunden\n";
    echo str_repeat("─", 100) . "\n\n";

    // Clear target table
    echo "🗑️  Leere Ziel-Tabelle (kp_club_management.matches)...\n";
    $targetDb->query("DELETE FROM matches");
    echo "✅ Tabelle geleert\n\n";

    // Get all matches from source
    echo "【2】 Lese alle 1501 Matches aus club_manager.matches\n";
    echo str_repeat("─", 100) . "\n\n";

    $result = $sourceDb->query("
        SELECT
            match_fifa_id,
            competition_fifa_id,
            team_fifa_id_home,
            team_name_home,
            team_fifa_id_away,
            team_name_away,
            team_score_home,
            team_score_away,
            match_place,
            match_status,
            date_time_local,
            created_at,
            updated_at
        FROM matches
    ");

    $matches = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ " . count($matches) . " Matches gelesen\n\n";

    // Prepare insert into target
    echo "【3】 Einfügen in kp_club_management.matches\n";
    echo str_repeat("─", 100) . "\n\n";

    $insertStmt = $targetDb->prepare("
        INSERT INTO matches (
            match_fifa_id,
            competition_fifa_id,
            team_fifa_id_home,
            team_name_home,
            team_fifa_id_away,
            team_name_away,
            team_score_home,
            team_score_away,
            match_place,
            match_status,
            date_time_local,
            created_at,
            updated_at
        ) VALUES (
            :match_fifa_id,
            :competition_fifa_id,
            :team_fifa_id_home,
            :team_name_home,
            :team_fifa_id_away,
            :team_name_away,
            :team_score_home,
            :team_score_away,
            :match_place,
            :match_status,
            :date_time_local,
            :created_at,
            :updated_at
        )
    ");

    $insertedCount = 0;
    $errors = 0;

    foreach ($matches as $match) {
        try {
            $insertStmt->execute([
                ':match_fifa_id' => $match['match_fifa_id'],
                ':competition_fifa_id' => $match['competition_fifa_id'],
                ':team_fifa_id_home' => $match['team_fifa_id_home'],
                ':team_name_home' => $match['team_name_home'],
                ':team_fifa_id_away' => $match['team_fifa_id_away'],
                ':team_name_away' => $match['team_name_away'],
                ':team_score_home' => $match['team_score_home'],
                ':team_score_away' => $match['team_score_away'],
                ':match_place' => $match['match_place'],
                ':match_status' => $match['match_status'],
                ':date_time_local' => $match['date_time_local'],
                ':created_at' => $match['created_at'],
                ':updated_at' => $match['updated_at'],
            ]);

            $insertedCount++;

            if (($insertedCount % 200) === 0) {
                echo "  ✓ " . $insertedCount . " Matches eingefügt...\n";
            }
        } catch (Exception $e) {
            $errors++;
        }
    }

    echo "\n" . str_repeat("─", 100) . "\n";
    echo "【4】 Verifikation\n";
    echo str_repeat("─", 100) . "\n\n";

    // Verify in target database
    $result = $targetDb->query("SELECT COUNT(*) as total FROM matches");
    $total = $result->fetch(PDO::FETCH_ASSOC)['total'];

    echo "Matches in kp_club_management.matches: $total\n";
    echo "Erfolgreich übertragen: $insertedCount\n";
    echo "Fehler: $errors\n\n";

    if ($total > 0) {
        echo "✅ ERFOLGREICH!\n";
        echo "   $total Matches sind jetzt in kp_club_management.matches\n";
    }

    echo "\n" . str_repeat("═", 100) . "\n";
    echo "📊 ZIELDATENBANK STATUS\n";
    echo str_repeat("═", 100) . "\n\n";

    // Show stats from target
    $result = $targetDb->query("
        SELECT
            COUNT(*) as total,
            MIN(date_time_local) as earliest,
            MAX(date_time_local) as latest
        FROM matches
    ");
    $stats = $result->fetch(PDO::FETCH_ASSOC);

    echo "Total Matches: " . $stats['total'] . "\n";
    echo "Zeitraum: " . $stats['earliest'] . " bis " . $stats['latest'] . "\n\n";

    // Status distribution
    $result = $targetDb->query("
        SELECT match_status, COUNT(*) as count
        FROM matches
        GROUP BY match_status
        ORDER BY count DESC
    ");

    echo "Nach Status:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("  %s: %d\n", $row['match_status'], $row['count']);
    }

    echo "\n✅ Transfer abgeschlossen!\n\n";

} catch (PDOException $e) {
    echo "❌ Datenbankfehler: " . $e->getMessage() . "\n";
    exit(1);
}

?>
