<?php

// UPDATE TEAM LOGOS IN MATCHES
echo "⏱️  UPDATE TEAM LOGOS\n";
echo str_repeat("=", 60) . "\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$logoDir = 'C:\xampp\htdocs\kpkb3\public\images\kp_team_logo_images';
$logoWebPath = 'images/kp_team_logo_images';

// Build logo map: team_fifa_id => logo_path
$logoMap = [];

$extensions = ['png', 'jpg', 'jpeg', 'gif'];
foreach (glob($logoDir . '/*') as $file) {
    $filename = basename($file);
    $teamId = pathinfo($filename, PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Prefer PNG
    if (!isset($logoMap[$teamId]) || $ext === 'png') {
        $logoMap[$teamId] = $logoWebPath . '/' . $filename;
    }
}

echo "📊 Found " . count($logoMap) . " team logos\n\n";

// Get all unique team IDs from matches
$teamsQuery = "SELECT DISTINCT team_fifa_id_home as team_id FROM comet_matches WHERE team_fifa_id_home IS NOT NULL
               UNION
               SELECT DISTINCT team_fifa_id_away as team_id FROM comet_matches WHERE team_fifa_id_away IS NOT NULL
               ORDER BY team_id";

$teamsResult = $mysqli->query($teamsQuery);

$teamsWithLogos = 0;
$teamsWithoutLogos = 0;
$updated = 0;

while ($team = $teamsResult->fetch_assoc()) {
    $teamId = $team['team_id'];

    if (isset($logoMap[$teamId])) {
        $logoPath = $mysqli->real_escape_string($logoMap[$teamId]);

        // Update home team logos
        $updateHome = "UPDATE comet_matches
                       SET team_logo_home = '{$logoPath}'
                       WHERE team_fifa_id_home = {$teamId}
                       AND (team_logo_home IS NULL OR team_logo_home != '{$logoPath}')";

        $mysqli->query($updateHome);
        $homeUpdated = $mysqli->affected_rows;

        // Update away team logos
        $updateAway = "UPDATE comet_matches
                       SET team_logo_away = '{$logoPath}'
                       WHERE team_fifa_id_away = {$teamId}
                       AND (team_logo_away IS NULL OR team_logo_away != '{$logoPath}')";

        $mysqli->query($updateAway);
        $awayUpdated = $mysqli->affected_rows;

        $totalUpdated = $homeUpdated + $awayUpdated;

        if ($totalUpdated > 0) {
            echo "✅ Team {$teamId}: Updated {$totalUpdated} matches\n";
            $updated += $totalUpdated;
        }

        $teamsWithLogos++;
    } else {
        $teamsWithoutLogos++;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ TEAM LOGOS UPDATE COMPLETED\n\n";
echo "📊 Statistics:\n";
echo "   - Teams with logos: {$teamsWithLogos}\n";
echo "   - Teams without logos: {$teamsWithoutLogos}\n";
echo "   - Matches updated: {$updated}\n";

// Show some examples
echo "\n📋 Sample matches with logos:\n";
$sampleQuery = "SELECT
                    team_name_home,
                    team_logo_home,
                    team_name_away,
                    team_logo_away
                FROM comet_matches
                WHERE team_logo_home IS NOT NULL
                AND team_logo_away IS NOT NULL
                LIMIT 5";

$samples = $mysqli->query($sampleQuery);
while ($match = $samples->fetch_assoc()) {
    echo "  {$match['team_name_home']} vs {$match['team_name_away']}\n";
    echo "    Home: {$match['team_logo_home']}\n";
    echo "    Away: {$match['team_logo_away']}\n";
}

$mysqli->close();

?>
