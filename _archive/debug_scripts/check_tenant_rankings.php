<?php

echo "🔍 CHECKING RANKINGS IN TENANT NKPRIGORJEM\n";
echo str_repeat("=", 80) . "\n\n";

$tenant = new mysqli('localhost', 'root', '', 'tenant_nkprigorjem');
if ($tenant->connect_error) {
    die("❌ Connection failed: " . $tenant->connect_error);
}

// Check all rankings in tenant DB
echo "📊 ALL RANKINGS IN TENANT DB:\n";
echo str_repeat("-", 80) . "\n";

$result = $tenant->query("
    SELECT 
        id,
        competition_fifa_id,
        international_competition_name,
        position,
        team_fifa_id,
        international_team_name,
        team_image_logo,
        points,
        matches_played
    FROM comet_rankings
    ORDER BY competition_fifa_id, position
    LIMIT 20
");

$withName = 0;
$withoutName = 0;
$withLogo = 0;
$withoutLogo = 0;

echo sprintf("%-5s %-10s %-35s %-3s %-10s %-25s %-5s\n", 
    "Pos", "TeamID", "Competition", "Pts", "TeamName", "Logo", "Games");
echo str_repeat("-", 80) . "\n";

while ($row = $result->fetch_assoc()) {
    $hasName = !empty($row['international_team_name']);
    $hasLogo = !empty($row['team_image_logo']);
    
    if ($hasName) $withName++; else $withoutName++;
    if ($hasLogo) $withLogo++; else $withoutLogo++;
    
    $nameStatus = $hasName ? '✅' : '❌';
    $logoStatus = $hasLogo ? '✅' : '❌';
    
    $teamName = $hasName ? substr($row['international_team_name'], 0, 20) : 'NO NAME';
    $logo = $hasLogo ? '✅' : '❌';
    
    echo sprintf("%-5s %-10s %-35s %-3s %s %-20s %s\n",
        $row['position'],
        $row['team_fifa_id'],
        substr($row['international_competition_name'] ?? 'N/A', 0, 34),
        $row['points'],
        $nameStatus,
        $teamName,
        $logoStatus
    );
}

echo "\n📈 SUMMARY:\n";
echo "   Team Names: {$withName} with names, {$withoutName} without names\n";
echo "   Team Logos: {$withLogo} with logos, {$withoutLogo} without logos\n";

// Check specific examples
echo "\n📋 DETAILED EXAMPLES (first 5):\n";
echo str_repeat("-", 80) . "\n";

$result = $tenant->query("
    SELECT 
        team_fifa_id,
        international_team_name,
        team_image_logo,
        international_competition_name,
        position,
        points
    FROM comet_rankings
    ORDER BY id
    LIMIT 5
");

while ($row = $result->fetch_assoc()) {
    echo "Team ID: {$row['team_fifa_id']}\n";
    echo "  Name: " . ($row['international_team_name'] ?: '❌ MISSING') . "\n";
    echo "  Logo: " . ($row['team_image_logo'] ?: '❌ MISSING') . "\n";
    echo "  Competition: {$row['international_competition_name']}\n";
    echo "  Position: {$row['position']} ({$row['points']} pts)\n";
    echo str_repeat("-", 80) . "\n";
}

$tenant->close();
