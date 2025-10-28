<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "📊 GENERIERE SPIELER-STATISTIKEN AUS MATCH EVENTS\n";
echo str_repeat("═", 100) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "【1】 Berechne Spieler-Statistiken\n";
    echo str_repeat("─", 100) . "\n\n";

    // Clear old player_statistics
    $pdo->query("TRUNCATE TABLE player_statistics");

    // Get all player stats
    $result = $pdo->query("
        SELECT
            player_fifa_id,
            player_name,
            SUM(CASE WHEN event_type = 'Goal' THEN 1 ELSE 0 END) as goals,
            SUM(CASE WHEN event_type = 'Yellow' THEN 1 ELSE 0 END) as yellow_cards,
            SUM(CASE WHEN event_type = 'Red' THEN 1 ELSE 0 END) as red_cards,
            SUM(CASE WHEN event_type = 'Substitution' THEN 1 ELSE 0 END) as substitutions,
            SUM(CASE WHEN event_type = 'Penalty' THEN 1 ELSE 0 END) as penalties,
            COUNT(DISTINCT match_fifa_id) as appearances,
            COUNT(*) as total_events
        FROM match_events
        WHERE player_fifa_id IS NOT NULL
        GROUP BY player_fifa_id, player_name
        HAVING COUNT(DISTINCT match_fifa_id) > 0
    ");

    $stats = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ " . count($stats) . " Spieler mit Events\n\n";

    // Insert into player_statistics table
    $insertStmt = $pdo->prepare("
        INSERT INTO player_statistics (
            player_fifa_id,
            competition_fifa_id,
            player_name,
            goals,
            assists,
            matches_played,
            minutes_played,
            yellow_cards,
            red_cards,
            created_at,
            updated_at
        ) VALUES (
            :player_fifa_id,
            :competition_fifa_id,
            :player_name,
            :goals,
            :assists,
            :matches_played,
            :minutes_played,
            :yellow_cards,
            :red_cards,
            NOW(),
            NOW()
        )
    ");

    $insertedCount = 0;
    foreach ($stats as $stat) {
        try {
            $insertStmt->execute([
                ':player_fifa_id' => $stat['player_fifa_id'],
                ':competition_fifa_id' => 100629221, // PRVA ZAGREBAČKA LIGA
                ':player_name' => $stat['player_name'],
                ':goals' => $stat['goals'] ?? 0,
                ':assists' => 0, // Not available in events
                ':matches_played' => $stat['appearances'] ?? 0,
                ':minutes_played' => 0, // Not available
                ':yellow_cards' => $stat['yellow_cards'] ?? 0,
                ':red_cards' => $stat['red_cards'] ?? 0,
            ]);
            $insertedCount++;
        } catch (Exception $e) {
            // Skip on error
        }
    }

    echo "【2】 Verifikation\n";
    echo str_repeat("─", 100) . "\n\n";

    echo "✅ " . $insertedCount . " Spieler-Statistiken eingefügt\n\n";

    // Show top stats
    $result = $pdo->query("
        SELECT
            player_name,
            goals,
            yellow_cards,
            red_cards,
            matches_played
        FROM player_statistics
        ORDER BY goals DESC
        LIMIT 10
    ");

    echo "📊 Top 10 nach Toren:\n";
    $i = 1;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%2d. %30s | %d Tore | %d Gelb | %d Rot | %d Spiele\n",
            $i,
            $row['player_name'] ?? 'Unknown',
            $row['goals'],
            $row['yellow_cards'],
            $row['red_cards'],
            $row['matches_played']
        );
        $i++;
    }

    echo "\n" . str_repeat("═", 100) . "\n";
    echo "✅ FERTIG! Spieler-Statistiken generiert\n";
    echo str_repeat("═", 100) . "\n\n";

} catch (PDOException $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
    exit(1);
}

?>
