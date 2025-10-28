<?php

echo "\n" . str_repeat("═", 100) . "\n";
echo "⚽ BERECHNE TOP SCORERS AUS MATCH EVENTS\n";
echo str_repeat("═", 100) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=kp_club_management', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "【1】 Abrufen aller Goal Events\n";
    echo str_repeat("─", 100) . "\n\n";

    // Get all goal events
    $result = $pdo->query("
        SELECT
            player_fifa_id,
            player_name,
            COUNT(*) as goals,
            GROUP_CONCAT(DISTINCT match_fifa_id) as matches,
            MAX(created_at) as last_goal_date
        FROM match_events
        WHERE event_type = 'Goal'
        GROUP BY player_fifa_id, player_name
        ORDER BY goals DESC
    ");

    $topScorers = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ " . count($topScorers) . " Torschützen gefunden\n\n";

    echo "【2】 Top 20 Torschützen\n";
    echo str_repeat("─", 100) . "\n\n";

    for ($i = 0; $i < min(20, count($topScorers)); $i++) {
        $scorer = $topScorers[$i];
        echo sprintf("%2d. %30s %2d Tore\n",
            $i + 1,
            $scorer['player_name'],
            $scorer['goals']
        );
    }

    echo "\n" . str_repeat("─", 100) . "\n";
    echo "【3】 Statistik\n";
    echo str_repeat("─", 100) . "\n\n";

    $totalGoals = 0;
    $uniquePlayers = count($topScorers);
    foreach ($topScorers as $scorer) {
        $totalGoals += $scorer['goals'];
    }

    echo "📊 Insgesamt: " . $totalGoals . " Tore\n";
    echo "👥 Von: " . $uniquePlayers . " verschiedenen Spielern\n";
    echo "📈 Durchschnitt: " . round($totalGoals / $uniquePlayers, 2) . " Tore pro Spieler\n";

    // Top Scorer
    if (count($topScorers) > 0) {
        $top = $topScorers[0];
        echo "\n🏆 TOP SCORER:\n";
        echo "   " . $top['player_name'] . " mit " . $top['goals'] . " Toren\n";
    }

    echo "\n" . str_repeat("═", 100) . "\n";
    echo "✅ FERTIG! Top Scorers berechnet\n";
    echo str_repeat("═", 100) . "\n\n";

} catch (PDOException $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
    exit(1);
}

?>
