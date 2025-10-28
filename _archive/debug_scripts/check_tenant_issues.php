<?php

echo "🔍 CHECKING TENANT NKPRIGORJEM DATABASE ISSUES\n";
echo str_repeat("=", 80) . "\n\n";

$tenant = new mysqli('localhost', 'root', '', 'tenant_nkprigorjem');
if ($tenant->connect_error) {
    die("❌ Connection failed: " . $tenant->connect_error);
}

$prigorjeFifaId = 598;

// ============================================================================
// CHECK RANKINGS - LOGOS
// ============================================================================
echo "📊 RANKINGS - LOGO STATUS:\n";
echo str_repeat("-", 80) . "\n";

$result = $tenant->query("
    SELECT international_competition_name, international_team_name,
           team_image_logo, position, points
    FROM comet_rankings
    WHERE team_fifa_id = $prigorjeFifaId
    ORDER BY position
");

$withLogo = 0;
$withoutLogo = 0;

while ($row = $result->fetch_assoc()) {
    $hasLogo = !empty($row['team_image_logo']);
    $logoStatus = $hasLogo ? '✅' : '❌';

    if ($hasLogo) {
        $withLogo++;
    } else {
        $withoutLogo++;
    }

    echo "   {$logoStatus} Pos {$row['position']}: {$row['international_team_name']} - {$row['international_competition_name']}\n";
    if ($hasLogo) {
        echo "      Logo: {$row['team_image_logo']}\n";
    }
}

echo "\n   Summary: {$withLogo} with logos, {$withoutLogo} without logos\n\n";

// ============================================================================
// CHECK MATCHES - DATA QUALITY
// ============================================================================
echo "📊 MATCHES - DATA QUALITY:\n";
echo str_repeat("-", 80) . "\n";

// Check for missing dates
$result = $tenant->query("
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
    AND date_time_local IS NULL
");
$missingDates = $result->fetch_assoc()['count'];
echo "   Missing dates: {$missingDates}\n";

// Check for missing team names
$result = $tenant->query("
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
    AND (team_name_home IS NULL OR team_name_away IS NULL)
");
$missingTeams = $result->fetch_assoc()['count'];
echo "   Missing team names: {$missingTeams}\n";

// Check for missing logos
$result = $tenant->query("
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
    AND (team_logo_home IS NULL OR team_logo_away IS NULL)
");
$missingLogos = $result->fetch_assoc()['count'];
echo "   Missing team logos: {$missingLogos}\n";

// Check for missing match_day
$result = $tenant->query("
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
    AND match_day IS NULL
");
$missingMatchDay = $result->fetch_assoc()['count'];
echo "   Missing match_day: {$missingMatchDay}\n";

// Show sample matches with potential issues
echo "\n   Sample matches:\n";
$result = $tenant->query("
    SELECT match_fifa_id, date_time_local, match_day, match_status,
           team_name_home, team_score_home, team_logo_home,
           team_name_away, team_score_away, team_logo_away
    FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
    ORDER BY date_time_local DESC
    LIMIT 10
");

while ($row = $result->fetch_assoc()) {
    $date = $row['date_time_local'] ? date('Y-m-d', strtotime($row['date_time_local'])) : 'NO DATE';
    $matchDay = $row['match_day'] ?? 'N/A';
    $homeLogoPre = $row['team_logo_home'] ? '🖼️' : '❌';
    $awayLogoPre = $row['team_logo_away'] ? '🖼️' : '❌';

    echo "   MD{$matchDay} | {$date} | {$homeLogoPre}{$row['team_name_home']} {$row['team_score_home']}-{$row['team_score_away']} {$awayLogoPre}{$row['team_name_away']} ({$row['match_status']})\n";
}

// ============================================================================
// CHECK MATCHES - SORTING BY MATCH_DAY
// ============================================================================
echo "\n📊 MATCHES - BY MATCH_DAY (first competition):\n";
echo str_repeat("-", 80) . "\n";

$result = $tenant->query("
    SELECT DISTINCT international_competition_name, competition_fifa_id
    FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
    LIMIT 1
");

if ($comp = $result->fetch_assoc()) {
    echo "   Competition: {$comp['international_competition_name']}\n\n";

    $matchesResult = $tenant->query("
        SELECT match_day, COUNT(*) as count
        FROM comet_matches
        WHERE competition_fifa_id = {$comp['competition_fifa_id']}
        AND (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
        GROUP BY match_day
        ORDER BY match_day
    ");

    while ($row = $matchesResult->fetch_assoc()) {
        $md = $row['match_day'] ?? 'NULL';
        echo "   Matchday {$md}: {$row['count']} matches\n";
    }
}

$tenant->close();
