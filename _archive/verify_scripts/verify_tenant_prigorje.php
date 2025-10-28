<?php

echo "🔍 VERIFYING NK PRIGORJE DATA IN TENANT DATABASE\n";
echo str_repeat("=", 80) . "\n\n";

$tenantDb = 'tenant_nkprigorjem';
$prigorjeFifaId = 598;

$tenant = new mysqli('localhost', 'root', '', $tenantDb);
if ($tenant->connect_error) {
    die("❌ Connection failed: " . $tenant->connect_error);
}

// Check NK Prigorje matches
echo "📊 NK PRIGORJE MATCHES IN TENANT DB:\n";
echo str_repeat("-", 80) . "\n";

$result = $tenant->query("
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
");
$row = $result->fetch_assoc();
echo "   Total NK Prigorje matches: {$row['count']}\n\n";

// Show recent matches
$result = $tenant->query("
    SELECT date_time_local, team_name_home, team_score_home,
           team_name_away, team_score_away, match_status
    FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
    ORDER BY date_time_local DESC
    LIMIT 5
");

echo "   Recent matches:\n";
while ($row = $result->fetch_assoc()) {
    $date = $row['date_time_local'] ? date('Y-m-d', strtotime($row['date_time_local'])) : 'N/A';
    echo "   - {$date}: {$row['team_name_home']} {$row['team_score_home']}-{$row['team_score_away']} {$row['team_name_away']} ({$row['match_status']})\n";
}

// Check rankings
echo "\n📊 NK PRIGORJE RANKINGS IN TENANT DB:\n";
echo str_repeat("-", 80) . "\n";

$result = $tenant->query("
    SELECT international_competition_name, position, points, matches_played, team_image_logo
    FROM comet_rankings
    WHERE team_fifa_id = $prigorjeFifaId
");

while ($row = $result->fetch_assoc()) {
    $logo = $row['team_image_logo'] ? '🖼️' : '❌';
    echo "   {$logo} {$row['international_competition_name']}: Position {$row['position']} ({$row['points']} pts, {$row['matches_played']} games)\n";
}

// Check top scorers
echo "\n📊 NK PRIGORJE TOP SCORERS IN TENANT DB:\n";
echo str_repeat("-", 80) . "\n";

$result = $tenant->query("
    SELECT international_first_name, international_last_name, goals,
           international_competition_name, team_logo
    FROM comet_top_scorers
    WHERE club_id = $prigorjeFifaId
    ORDER BY goals DESC
    LIMIT 10
");

while ($row = $result->fetch_assoc()) {
    $logo = $row['team_logo'] ? '🖼️' : '❌';
    echo "   {$logo} {$row['international_first_name']} {$row['international_last_name']}: {$row['goals']} goals ({$row['international_competition_name']})\n";
}

$tenant->close();

echo "\n✅ Verification complete!\n";
