<?php

require_once __DIR__ . '/lib/sync_helpers.php';

echo "⏱️  SYNC NK PRIGORJE DATA TO TENANT DATABASE\n";
echo str_repeat("=", 80) . "\n\n";

// Configuration
$prigorjeFifaId = 598; // NK Prigorje Markuševec
$landlordDb = 'kpkb3';
$tenantDb = 'tenant_nkprigorjem';

// Connect to landlord DB
$landlord = new mysqli('localhost', 'root', '', $landlordDb);
if ($landlord->connect_error) {
    die("❌ Landlord connection failed: " . $landlord->connect_error);
}

// Connect to tenant DB
$tenant = new mysqli('localhost', 'root', '', $tenantDb);
if ($tenant->connect_error) {
    die("❌ Tenant connection failed: " . $tenant->connect_error);
}

echo "✅ Connected to databases\n\n";

// Statistics
$stats = [
    'matches' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
    'rankings' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
    'top_scorers' => ['inserted' => 0, 'updated' => 0, 'skipped' => 0],
];

// ============================================================================
// SYNC MATCHES
// ============================================================================
echo "📊 SYNCING MATCHES\n";
echo str_repeat("-", 80) . "\n";

$matchesQuery = "
    SELECT * FROM comet_matches
    WHERE team_fifa_id_home = $prigorjeFifaId OR team_fifa_id_away = $prigorjeFifaId
    ORDER BY date_time_local DESC
";

$matchesResult = $landlord->query($matchesQuery);
$totalMatches = $matchesResult->num_rows;
echo "Found {$totalMatches} matches to sync...\n";

while ($match = $matchesResult->fetch_assoc()) {
    // Remove auto-increment ID
    $matchId = $match['id'];
    unset($match['id']);

    $keyCols = ['match_fifa_id' => $match['match_fifa_id']];

    try {
        $result = upsert_if_changed($tenant, 'comet_matches', $keyCols, $match);
        $stats['matches'][$result['action']]++;
    } catch (Exception $e) {
        echo "⚠️  Error syncing match {$match['match_fifa_id']}: {$e->getMessage()}\n";
    }
}

echo "✅ Matches synced: {$stats['matches']['inserted']} inserted, {$stats['matches']['updated']} updated, {$stats['matches']['skipped']} skipped\n\n";

// ============================================================================
// SYNC RANKINGS - ALL TEAMS IN PRIGORJE'S COMPETITIONS
// ============================================================================
echo "📊 SYNCING RANKINGS\n";
echo str_repeat("-", 80) . "\n";

// First, get all competitions where NK Prigorje plays
$competitionsQuery = "
    SELECT DISTINCT competition_fifa_id 
    FROM comet_rankings 
    WHERE team_fifa_id = $prigorjeFifaId
";
$competitionsResult = $landlord->query($competitionsQuery);
$competitionIds = [];
while ($row = $competitionsResult->fetch_assoc()) {
    $competitionIds[] = $row['competition_fifa_id'];
}

echo "Found " . count($competitionIds) . " competitions where NK Prigorje plays\n";

// Now sync ALL rankings from these competitions (all teams)
$competitionIdsList = implode(',', $competitionIds);
$rankingsQuery = "
    SELECT * FROM comet_rankings
    WHERE competition_fifa_id IN ($competitionIdsList)
";

$rankingsResult = $landlord->query($rankingsQuery);
$totalRankings = $rankingsResult->num_rows;
echo "Found {$totalRankings} total rankings (all teams) to sync...\n";

while ($ranking = $rankingsResult->fetch_assoc()) {
    // Remove auto-increment ID
    unset($ranking['id']);

    $keyCols = [
        'competition_fifa_id' => $ranking['competition_fifa_id'],
        'team_fifa_id' => $ranking['team_fifa_id']
    ];

    try {
        $result = upsert_if_changed($tenant, 'comet_rankings', $keyCols, $ranking);
        $stats['rankings'][$result['action']]++;
    } catch (Exception $e) {
        echo "⚠️  Error syncing ranking: {$e->getMessage()}\n";
    }
}

echo "✅ Rankings synced: {$stats['rankings']['inserted']} inserted, {$stats['rankings']['updated']} updated, {$stats['rankings']['skipped']} skipped\n\n";

// ============================================================================
// SYNC TOP SCORERS
// ============================================================================
echo "📊 SYNCING TOP SCORERS\n";
echo str_repeat("-", 80) . "\n";

$scorersQuery = "
    SELECT * FROM comet_top_scorers
    WHERE club_id = $prigorjeFifaId
";

$scorersResult = $landlord->query($scorersQuery);
$totalScorers = $scorersResult->num_rows;
echo "Found {$totalScorers} top scorers to sync...\n";

while ($scorer = $scorersResult->fetch_assoc()) {
    // Remove auto-increment ID
    unset($scorer['id']);

    $keyCols = [
        'competition_fifa_id' => $scorer['competition_fifa_id'],
        'player_fifa_id' => $scorer['player_fifa_id']
    ];

    try {
        $result = upsert_if_changed($tenant, 'comet_top_scorers', $keyCols, $scorer);
        $stats['top_scorers'][$result['action']]++;
    } catch (Exception $e) {
        echo "⚠️  Error syncing top scorer: {$e->getMessage()}\n";
    }
}

echo "✅ Top scorers synced: {$stats['top_scorers']['inserted']} inserted, {$stats['top_scorers']['updated']} updated, {$stats['top_scorers']['skipped']} skipped\n\n";

// ============================================================================
// SUMMARY
// ============================================================================
echo str_repeat("=", 80) . "\n";
echo "✅ NK PRIGORJE TENANT SYNC COMPLETED\n\n";

echo "📊 FINAL STATISTICS:\n";
echo "   Matches:      {$stats['matches']['inserted']} inserted, {$stats['matches']['updated']} updated, {$stats['matches']['skipped']} skipped\n";
echo "   Rankings:     {$stats['rankings']['inserted']} inserted, {$stats['rankings']['updated']} updated, {$stats['rankings']['skipped']} skipped\n";
echo "   Top Scorers:  {$stats['top_scorers']['inserted']} inserted, {$stats['top_scorers']['updated']} updated, {$stats['top_scorers']['skipped']} skipped\n\n";

// Verify tenant DB counts
echo "📈 TENANT DATABASE STATUS:\n";
$matchCount = $tenant->query("SELECT COUNT(*) as count FROM comet_matches")->fetch_assoc()['count'];
$rankingCount = $tenant->query("SELECT COUNT(*) as count FROM comet_rankings")->fetch_assoc()['count'];
$scorerCount = $tenant->query("SELECT COUNT(*) as count FROM comet_top_scorers")->fetch_assoc()['count'];

echo "   Total matches:     {$matchCount}\n";
echo "   Total rankings:    {$rankingCount}\n";
echo "   Total top scorers: {$scorerCount}\n\n";

$landlord->close();
$tenant->close();
