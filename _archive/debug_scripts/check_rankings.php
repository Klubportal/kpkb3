<?php

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🔍 CHECKING COMET_RANKINGS TABLE\n";
echo str_repeat("=", 80) . "\n\n";

// Check table structure
echo "📋 Table Structure:\n";
$columns = $mysqli->query("SHOW COLUMNS FROM comet_rankings");
while ($col = $columns->fetch_assoc()) {
    echo "   - {$col['Field']} ({$col['Type']})\n";
}
echo "\n";

// Check sample data
echo "📊 Sample Data (first 5 rows):\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("SELECT * FROM comet_rankings LIMIT 5");

while ($row = $result->fetch_assoc()) {
    echo "Position: {$row['position']} | Team: {$row['international_team_name']} | FifaID: {$row['team_fifa_id']}\n";
    echo "   Logo: " . ($row['team_image_logo'] ?? 'NULL') . "\n";
    echo "   Points: {$row['points']} | Matches: {$row['matches_played']}\n";
    echo str_repeat("-", 80) . "\n";
}

// Count with/without logos
$withLogo = $mysqli->query("SELECT COUNT(*) as count FROM comet_rankings WHERE team_image_logo IS NOT NULL")->fetch_assoc()['count'];
$withoutLogo = $mysqli->query("SELECT COUNT(*) as count FROM comet_rankings WHERE team_image_logo IS NULL")->fetch_assoc()['count'];
$total = $mysqli->query("SELECT COUNT(*) as count FROM comet_rankings")->fetch_assoc()['count'];

echo "\n📈 Logo Statistics:\n";
echo "   - Total rankings: {$total}\n";
echo "   - With logos: {$withLogo}\n";
echo "   - Without logos: {$withoutLogo}\n";

$mysqli->close();
