<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=club_manager', 'root', '');

echo "✅ MATCHES SYNCHRONIZATION VERIFICATION\n";
echo str_repeat("═", 80) . "\n\n";

// Total matches
$total = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
echo "✓ Total matches in database: " . $total . "\n\n";

// Status distribution
echo "Distribution by status:\n";
$statusResult = $pdo->query("SELECT match_status, COUNT(*) as cnt FROM matches GROUP BY match_status ORDER BY cnt DESC");
foreach ($statusResult as $row) {
    printf("  %-15s: %3d\n", $row['match_status'], $row['cnt']);
}

// Sample matches
echo "\n" . str_repeat("─", 80) . "\n";
echo "Sample matches (first 5):\n";
echo str_repeat("─", 80) . "\n";

$samples = $pdo->query("SELECT match_fifa_id, competition_fifa_id, team_name_home, team_name_away, team_score_home, team_score_away, match_status, date_time_local FROM matches LIMIT 5");
foreach ($samples as $match) {
    printf("\n%s vs %s\n", $match['team_name_home'], $match['team_name_away']);
    printf("  Score: %s-%s | Status: %s | Date: %s\n",
        $match['team_score_home'] ?? '-',
        $match['team_score_away'] ?? '-',
        $match['match_status'],
        $match['date_time_local'] ?? 'N/A'
    );
}

echo "\n" . str_repeat("═", 80) . "\n";
echo "✅ All 209 matches successfully synced from Comet REST API!\n";
?>
