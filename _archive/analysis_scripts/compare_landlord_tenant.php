<?php

echo "🔍 COMPARING LANDLORD vs TENANT DATA\n";
echo str_repeat("=", 80) . "\n\n";

$landlord = new mysqli('localhost', 'root', '', 'kpkb3');
$tenant = new mysqli('localhost', 'root', '', 'tenant_nkprigorjem');
$prigorjeFifaId = 598;

// Check missing dates
echo "📊 MISSING DATES:\n";
$result = $landlord->query("
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
    AND date_time_local IS NULL
");
$landlordMissing = $result->fetch_assoc()['count'];

$result = $tenant->query("
    SELECT COUNT(*) as count
    FROM comet_matches
    WHERE (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
    AND date_time_local IS NULL
");
$tenantMissing = $result->fetch_assoc()['count'];

echo "   Landlord: {$landlordMissing} matches without date\n";
echo "   Tenant:   {$tenantMissing} matches without date\n";

if ($landlordMissing > 0) {
    echo "\n   These matches have no date in source (API issue):\n";
    $result = $landlord->query("
        SELECT match_fifa_id, team_name_home, team_name_away, match_status, match_day
        FROM comet_matches
        WHERE (team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId)
        AND date_time_local IS NULL
        LIMIT 10
    ");

    while ($row = $result->fetch_assoc()) {
        echo "   - MD{$row['match_day']}: {$row['team_name_home']} vs {$row['team_name_away']} ({$row['match_status']})\n";
    }
}

// Check for data differences
echo "\n📊 DATA SYNC STATUS:\n";
$result = $landlord->query("SELECT COUNT(*) as count FROM comet_matches WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId");
$landlordCount = $result->fetch_assoc()['count'];

$result = $tenant->query("SELECT COUNT(*) as count FROM comet_matches WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId");
$tenantCount = $result->fetch_assoc()['count'];

echo "   Landlord NK Prigorje matches: {$landlordCount}\n";
echo "   Tenant NK Prigorje matches:   {$tenantCount}\n";

if ($landlordCount == $tenantCount) {
    echo "   ✅ Match counts are equal\n";
} else {
    echo "   ⚠️  Match counts differ by " . abs($landlordCount - $tenantCount) . "\n";
}

$landlord->close();
$tenant->close();
