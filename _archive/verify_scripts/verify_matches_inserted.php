<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=club_manager', 'root', '');

echo "\n✅ MATCHES VERIFICATION\n";
echo str_repeat("═", 80) . "\n\n";

// Check total count
$result = $pdo->query("SELECT COUNT(*) as total FROM matches");
$total = $result->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total matches: $total\n";

// Check by status
$result = $pdo->query("
    SELECT match_status, COUNT(*) as count
    FROM matches
    GROUP BY match_status
    ORDER BY count DESC
");
echo "\nBy Status:\n";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . $row['match_status'] . ": " . $row['count'] . "\n";
}

// Check date range
$result = $pdo->query("
    SELECT MIN(date_time_local) as earliest, MAX(date_time_local) as latest
    FROM matches
");
$dates = $result->fetch(PDO::FETCH_ASSOC);
echo "\nDate Range:\n";
echo "  Earliest: " . $dates['earliest'] . "\n";
echo "  Latest: " . $dates['latest'] . "\n";

// Sample match with all fields
echo "\n" . str_repeat("─", 80) . "\n";
echo "Sample Match (first record):\n";
echo str_repeat("─", 80) . "\n";

$result = $pdo->query("
    SELECT match_fifa_id, competition_fifa_id,
           team_fifa_id_home, team_name_home, team_score_home,
           team_fifa_id_away, team_name_away, team_score_away,
           date_time_local, match_status, match_place
    FROM matches
    LIMIT 1
");

$match = $result->fetch(PDO::FETCH_ASSOC);
foreach ($match as $key => $value) {
    echo sprintf("  %-25s: %s\n", $key, $value ?? 'NULL');
}

// Check competitions referenced
echo "\n" . str_repeat("─", 80) . "\n";
echo "Matches per Competition:\n";
echo str_repeat("─", 80) . "\n";

$result = $pdo->query("
    SELECT competition_fifa_id, COUNT(*) as count
    FROM matches
    GROUP BY competition_fifa_id
    ORDER BY competition_fifa_id
");

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "  FIFA " . $row['competition_fifa_id'] . ": " . $row['count'] . " matches\n";
}

echo "\n✅ Verification Complete!\n\n";

?>
