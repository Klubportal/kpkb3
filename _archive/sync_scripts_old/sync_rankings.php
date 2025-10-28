<?php

require_once __DIR__ . '/lib/sync_helpers.php';

// SYNC COMET RANKINGS
echo "⏱️  SYNC COMET RANKINGS\n";
echo str_repeat("=", 60) . "\n\n";

// MySQL Verbindung
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// API Settings
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Build team logo map
echo "📷 Loading team logos...\n";
$logoDir = __DIR__ . '/public/images/kp_team_logo_images';
$logoWebPath = 'images/kp_team_logo_images';
$logoMap = [];

foreach (glob($logoDir . '/*') as $file) {
    $filename = basename($file);
    $teamId = pathinfo($filename, PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Prefer PNG
    if (!isset($logoMap[$teamId]) || $ext === 'png') {
        $logoMap[$teamId] = $logoWebPath . '/' . $filename;
    }
}
echo "   Found " . count($logoMap) . " team logos\n\n";

// Get active competitions
$competitionsQuery = "SELECT competitionFifaId, internationalName, ageCategoryName
                      FROM comet_club_competitions
                      WHERE status = 'active'";
$competitionsResult = $mysqli->query($competitionsQuery);

if (!$competitionsResult) {
    die("Query Error: " . $mysqli->error . "\n");
}

$competitions = [];
while ($row = $competitionsResult->fetch_assoc()) {
    $competitions[] = $row;
}

echo "📊 Active Competitions: " . count($competitions) . "\n\n";

$statsInserted = 0;
$statsUpdated = 0;
$statsSkipped = 0;
$errors = 0;
$totalRankings = 0;

foreach ($competitions as $comp) {
    echo "📋 {$comp['internationalName']}\n";
    echo str_repeat('-', 60) . "\n";

    // API Call - Competition Rankings
    $ch = curl_init("{$apiUrl}/competition/{$comp['competitionFifaId']}/ranking");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        echo "❌ HTTP {$httpCode}\n\n";
        $errors++;
        continue;
    }

    $rankings = json_decode($response, true);
    if (empty($rankings)) {
        echo "ℹ️  No rankings found\n\n";
        continue;
    }

    echo "📥 Processing " . count($rankings) . " ranking entries...\n";
    $totalRankings += count($rankings);

    foreach ($rankings as $rank) {
        // Prepare data
        $keyCols = [
            'competition_fifa_id' => $comp['competitionFifaId'],
            'team_fifa_id' => $rank['teamFifaId']
        ];

        // Get team logo
        $teamFifaId = $rank['teamFifaId'];
        $teamLogo = isset($logoMap[$teamFifaId]) ? $logoMap[$teamFifaId] : null;

        // Get team short name from nested structure (prefer short name)
        $teamName = $rank['team']['internationalShortName'] ?? $rank['team']['internationalName'] ?? null;

        $data = [
            'competition_fifa_id' => $comp['competitionFifaId'],
            'team_fifa_id' => $rank['teamFifaId'],
            'international_team_name' => $teamName,
            'team_image_logo' => $teamLogo,
            'position' => $rank['position'] ?? null,
            'matches_played' => $rank['matchesPlayed'] ?? null,
            'wins' => $rank['wins'] ?? null,
            'draws' => $rank['draws'] ?? null,
            'losses' => $rank['losses'] ?? null,
            'goals_for' => $rank['goalsFor'] ?? null,
            'goals_against' => $rank['goalsAgainst'] ?? null,
            'goal_difference' => $rank['goalDifference'] ?? null,
            'points' => $rank['points'] ?? null
        ];

        try {
            $result = upsert_if_changed($mysqli, 'comet_rankings', $keyCols, $data);

            if ($result['action'] === 'inserted') {
                $statsInserted++;
            } elseif ($result['action'] === 'updated') {
                $statsUpdated++;
            } else {
                $statsSkipped++;
            }

        } catch (Exception $e) {
            echo "⚠️  Error upserting ranking for team {$rank['teamFifaId']}: " . $e->getMessage() . "\n";
            $errors++;
        }
    }

    echo "✅ Processed " . count($rankings) . " entries\n\n";

    // Pause between competitions
    usleep(200000); // 200ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ COMET RANKINGS SYNC COMPLETED\n\n";
echo "📊 Statistics:\n";
echo "   - Competitions processed: " . count($competitions) . "\n";
echo "   - Total ranking entries: {$totalRankings}\n";
echo "   - Inserted: {$statsInserted}\n";
echo "   - Updated: {$statsUpdated}\n";
echo "   - Skipped (unchanged): {$statsSkipped}\n";
echo "   - Errors: {$errors}\n\n";

// Final count
$finalCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_rankings")->fetch_assoc()['count'];

echo "📈 DATABASE STATUS:\n";
echo "   - Total ranking entries: {$finalCount}\n";

$mysqli->close();

?>
