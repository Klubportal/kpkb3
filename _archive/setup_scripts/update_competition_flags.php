<?php

// UPDATE COMPETITION FLAGS
echo "⏱️  UPDATE COMPETITION FLAGS\n";
echo str_repeat("=", 60) . "\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get all competitions
$competitionsQuery = "SELECT competitionFifaId, internationalName FROM comet_club_competitions";
$competitionsResult = $mysqli->query($competitionsQuery);

$updated = 0;

while ($comp = $competitionsResult->fetch_assoc()) {
    $compId = $comp['competitionFifaId'];

    // Count played matches
    $playedQuery = "SELECT COUNT(*) as count
                    FROM comet_matches
                    WHERE competition_fifa_id = {$compId}
                    AND match_status = 'played'";
    $playedCount = $mysqli->query($playedQuery)->fetch_assoc()['count'];

    // Count scheduled matches
    $scheduledQuery = "SELECT COUNT(*) as count
                       FROM comet_matches
                       WHERE competition_fifa_id = {$compId}
                       AND match_status = 'scheduled'";
    $scheduledCount = $mysqli->query($scheduledQuery)->fetch_assoc()['count'];

    // Update competition
    $updateQuery = "UPDATE comet_club_competitions
                    SET flag_played_matches = {$playedCount},
                        flag_scheduled_matches = {$scheduledCount},
                        updated_at = NOW()
                    WHERE competitionFifaId = {$compId}";

    if ($mysqli->query($updateQuery)) {
        echo "✅ {$comp['internationalName']}: {$playedCount} played, {$scheduledCount} scheduled\n";
        $updated++;
    } else {
        echo "❌ Error updating {$comp['internationalName']}: " . $mysqli->error . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Updated {$updated} competitions\n";

$mysqli->close();

?>
