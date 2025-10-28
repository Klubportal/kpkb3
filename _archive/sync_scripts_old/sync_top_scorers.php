<?php

require_once __DIR__ . '/lib/sync_helpers.php';

// SYNC COMET TOP SCORERS
echo "⏱️  SYNC COMET TOP SCORERS\n";
echo str_repeat("=", 60) . "\n\n";

// MySQL Verbindung
$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Load team logos
echo "🖼️  Loading team logos...\n";
$logoDir = __DIR__ . '/public/images/kp_team_logo_images';
$logoMap = []; // team_fifa_id => logo_path

if (is_dir($logoDir)) {
    $logoFiles = glob($logoDir . '/*');

    foreach ($logoFiles as $logoFile) {
        $filename = basename($logoFile);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Extract team ID from filename (assuming format: teamId.ext)
        $teamId = pathinfo($filename, PATHINFO_FILENAME);

        // Only map valid image extensions
        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif'])) {
            // Prefer PNG if team already exists
            if (!isset($logoMap[$teamId]) || $extension === 'png') {
                $logoMap[$teamId] = '/images/kp_team_logo_images/' . $filename;
            }
        }
    }
}

$logoCount = count($logoMap);
echo "   Found {$logoCount} team logos\n\n";

// API Settings
$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

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
$totalScorers = 0;

foreach ($competitions as $comp) {
    echo "📋 {$comp['internationalName']}\n";
    echo str_repeat('-', 60) . "\n";

    // API Call - Top Scorers
    $ch = curl_init("{$apiUrl}/competition/{$comp['competitionFifaId']}/topScorers");
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

    $scorers = json_decode($response, true);
    if (empty($scorers)) {
        echo "ℹ️  No top scorers found\n\n";
        continue;
    }

    echo "📥 Processing " . count($scorers) . " top scorers...\n";
    $totalScorers += count($scorers);

    $localInserted = 0;
    $localUpdated = 0;
    $localSkipped = 0;

    foreach ($scorers as $scorer) {
        // Skip entries without player ID
        if (empty($scorer['personFifaId'])) {
            continue;
        }

        // Prepare data
        $keyCols = [
            'competition_fifa_id' => $comp['competitionFifaId'],
            'player_fifa_id' => $scorer['personFifaId']
        ];

        // Extract first/last name
        $fullName = $scorer['internationalName'] ?? '';
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        // Get team logo
        $clubId = $scorer['teamFifaId'] ?? null;
        $teamLogo = ($clubId && isset($logoMap[$clubId])) ? $logoMap[$clubId] : null;

        $data = [
            'competition_fifa_id' => $comp['competitionFifaId'],
            'international_competition_name' => $comp['internationalName'],
            'age_category' => $comp['ageCategoryName'],
            'age_category_name' => $comp['ageCategoryName'],
            'player_fifa_id' => $scorer['personFifaId'],
            'goals' => $scorer['goals'] ?? 0,
            'international_first_name' => $firstName,
            'international_last_name' => $lastName,
            'club' => $scorer['teamInternationalName'] ?? null,
            'club_id' => $scorer['teamFifaId'] ?? null,
            'team_logo' => $teamLogo
        ];

        try {
            $result = upsert_if_changed($mysqli, 'comet_top_scorers', $keyCols, $data);

            if ($result['action'] === 'inserted') {
                $statsInserted++;
                $localInserted++;
            } elseif ($result['action'] === 'updated') {
                $statsUpdated++;
                $localUpdated++;
            } else {
                $statsSkipped++;
                $localSkipped++;
            }

        } catch (Exception $e) {
            echo "⚠️  Error upserting scorer {$scorer['personFifaId']}: " . $e->getMessage() . "\n";
            $errors++;
        }
    }

    echo "✅ Processed " . count($scorers) . " scorers (I:{$localInserted} U:{$localUpdated} S:{$localSkipped})\n\n";

    // Pause between competitions
    usleep(200000); // 200ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ COMET TOP SCORERS SYNC COMPLETED\n\n";
echo "📊 Statistics:\n";
echo "   - Competitions processed: " . count($competitions) . "\n";
echo "   - Total top scorers: {$totalScorers}\n";
echo "   - Inserted: {$statsInserted}\n";
echo "   - Updated: {$statsUpdated}\n";
echo "   - Skipped (unchanged): {$statsSkipped}\n";
echo "   - Errors: {$errors}\n\n";

// Final count
$finalCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_top_scorers")->fetch_assoc()['count'];

echo "📈 DATABASE STATUS:\n";
echo "   - Total top scorers: {$finalCount}\n";

$mysqli->close();

?>
