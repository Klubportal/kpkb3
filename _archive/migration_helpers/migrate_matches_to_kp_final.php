<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "🔄 ERSTELLE NEUE MATCHES TABELLE IN kp_club_management\n";
echo str_repeat("═", 100) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "【1】 Backup und Tabelle vorbereiten\n";
    echo str_repeat("─", 100) . "\n\n";

    // Drop old table
    echo "🗑️  Lösche alte matches Tabelle...\n";
    $pdo->query("DROP TABLE IF EXISTS matches");
    echo "✅ Alte Tabelle gelöscht\n\n";

    // Create new table with correct schema
    echo "📝 Erstelle neue matches Tabelle...\n";
    $createTable = "
        CREATE TABLE matches (
            id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            match_fifa_id INT UNIQUE,
            competition_fifa_id INT,
            team_fifa_id_home INT,
            team_name_home VARCHAR(255),
            team_fifa_id_away INT,
            team_name_away VARCHAR(255),
            team_score_home INT,
            team_score_away INT,
            match_place VARCHAR(255),
            match_status VARCHAR(50),
            date_time_local DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_competition (competition_fifa_id),
            INDEX idx_home_team (team_fifa_id_home),
            INDEX idx_away_team (team_fifa_id_away),
            INDEX idx_status (match_status),
            INDEX idx_date (date_time_local)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($createTable);
    echo "✅ Neue Tabelle erstellt\n\n";

    echo "【2】 Lade 1501 Matches aus club_manager\n";
    echo str_repeat("─", 100) . "\n\n";

    $sourceDb = new PDO('mysql:host=127.0.0.1;dbname=club_manager', 'root', '');
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
    echo "✅ " . count($matches) . " Matches geladen\n\n";

    echo "【3】 Einfügen in kp_club_management.matches\n";
    echo str_repeat("─", 100) . "\n\n";

    $insertStmt = $pdo->prepare("
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

    $result = $pdo->query("SELECT COUNT(*) as total FROM matches");
    $total = $result->fetch(PDO::FETCH_ASSOC)['total'];

    echo "✅ Matches in kp_club_management.matches: " . $total . "\n";
    echo "   Erfolgreich eingefügt: " . $insertedCount . "\n";
    echo "   Fehler: " . $errors . "\n\n";

    if ($total > 0) {
        // Show stats
        $result = $pdo->query("
            SELECT
                COUNT(*) as total,
                MIN(date_time_local) as earliest,
                MAX(date_time_local) as latest
            FROM matches
        ");
        $stats = $result->fetch(PDO::FETCH_ASSOC);

        echo "📊 Statistik:\n";
        echo "   Total: " . $stats['total'] . "\n";
        echo "   Zeitraum: " . $stats['earliest'] . " bis " . $stats['latest'] . "\n\n";

        $result = $pdo->query("
            SELECT match_status, COUNT(*) as count
            FROM matches
            GROUP BY match_status
            ORDER BY count DESC
        ");

        echo "   Nach Status:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo sprintf("     %s: %d\n", $row['match_status'], $row['count']);
        }

        echo "\n" . str_repeat("═", 100) . "\n";
        echo "✅ FERTIG! 1501 Matches sind jetzt in kp_club_management.matches\n";
        echo str_repeat("═", 100) . "\n\n";
    }

} catch (PDOException $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
    exit(1);
}

?>
