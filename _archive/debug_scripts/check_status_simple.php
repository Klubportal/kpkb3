<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🔍 MATCH STATUS CHECK\n";
echo str_repeat("=", 60) . "\n\n";

// Check if match_status column exists
echo "📋 Checking columns:\n";
$columns = $mysqli->query("SHOW COLUMNS FROM comet_matches LIKE '%status%'");
while ($col = $columns->fetch_assoc()) {
    echo "   Column: {$col['Field']} | Type: {$col['Type']}\n";
}
echo "\n";

// Get status distribution
echo "📊 Status distribution (ALL matches):\n";
$result = $mysqli->query("SELECT match_status, COUNT(*) as count FROM comet_matches GROUP BY match_status ORDER BY count DESC");
$total = 0;
while ($row = $result->fetch_assoc()) {
    echo "   {$row['match_status']}: {$row['count']}\n";
    $total += $row['count'];
}
echo "   TOTAL: {$total}\n\n";

// Check NK Prigorje specifically
$teamFifaId = 598;
echo "📊 NK Prigorje (Team 598) status:\n";
$result = $mysqli->query("
    SELECT match_status, COUNT(*) as count
    FROM comet_matches
    WHERE team_fifa_id_home = {$teamFifaId} OR team_fifa_id_away = {$teamFifaId}
    GROUP BY match_status
    ORDER BY count DESC
");
$nkTotal = 0;
while ($row = $result->fetch_assoc()) {
    echo "   {$row['match_status']}: {$row['count']}\n";
    $nkTotal += $row['count'];
}
echo "   TOTAL: {$nkTotal}\n\n";

// Check match_phases count
$phaseResult = $mysqli->query("SELECT COUNT(*) as count FROM comet_match_phases");
$phaseCount = $phaseResult->fetch_assoc()['count'];
echo "📊 Current match_phases: {$phaseCount}\n";

$mysqli->close();
