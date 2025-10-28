<?php

require_once __DIR__ . '/lib/sync_helpers.php';

// SYNC COMET MATCHES
echo "⏱️  SYNC COMET MATCHES\n";
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
$competitionsQuery = "SELECT competitionFifaId, season, ageCategory, ageCategoryName, internationalName
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
$totalMatches = 0;

foreach ($competitions as $comp) {
    echo "📋 {$comp['internationalName']} ({$comp['ageCategoryName']})\n";
    echo str_repeat('-', 60) . "\n";

    // API Call - Competition Matches
    $ch = curl_init("{$apiUrl}/competition/{$comp['competitionFifaId']}/matches");
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

    $matches = json_decode($response, true);
    if (empty($matches)) {
        echo "ℹ️  No matches found\n\n";
        continue;
    }

    echo "📥 Processing " . count($matches) . " matches...\n";
    $totalMatches += count($matches);

    foreach ($matches as $match) {
        // Extract home/away teams
        $homeTeamId = null;
        $awayTeamId = null;
        $homeTeamName = null;
        $awayTeamName = null;

        if (isset($match['matchTeams']) && is_array($match['matchTeams'])) {
            foreach ($match['matchTeams'] as $teamData) {
                $teamId = $teamData['teamFifaId'] ?? null;
                $teamNature = $teamData['teamNature'] ?? null;
                // Use short name if available, otherwise full name
                $teamName = $teamData['team']['internationalShortName'] ?? $teamData['team']['internationalName'] ?? null;

                if ($teamNature === 'HOME') {
                    $homeTeamId = $teamId;
                    $homeTeamName = $teamName;
                } elseif ($teamNature === 'AWAY') {
                    $awayTeamId = $teamId;
                    $awayTeamName = $teamName;
                }
            }
        }

        // Prepare data
        $keyCols = [
            'match_fifa_id' => $match['matchFifaId']
        ];

        $dateTimeLocal = null;
        if (!empty($match['dateTimeLocal'])) {
            $dateTimeLocal = date('Y-m-d H:i:s', strtotime($match['dateTimeLocal']));
        }

        // Get team logos
        $homeLogo = isset($logoMap[$homeTeamId]) ? $logoMap[$homeTeamId] : null;
        $awayLogo = isset($logoMap[$awayTeamId]) ? $logoMap[$awayTeamId] : null;

        $data = [
            'match_fifa_id' => $match['matchFifaId'],
            'competition_fifa_id' => $comp['competitionFifaId'],
            'age_category' => $comp['ageCategory'],
            'age_category_name' => $comp['ageCategoryName'],
            'international_competition_name' => $comp['internationalName'],
            'season' => $comp['season'],
            'competition_status' => 'active',
            'match_status' => strtolower($match['status'] ?? 'unknown'),
            'match_day' => $match['matchDay'] ?? null,
            'match_place' => $match['facilityFifaId'] ?? null,
            'date_time_local' => $dateTimeLocal,
            'team_fifa_id_home' => $homeTeamId,
            'team_name_home' => $homeTeamName,
            'team_score_home' => $match['homeFinalResult'] ?? null,
            'team_logo_home' => $homeLogo,
            'team_fifa_id_away' => $awayTeamId,
            'team_name_away' => $awayTeamName,
            'team_score_away' => $match['awayFinalResult'] ?? null,
            'team_logo_away' => $awayLogo
        ];

        try {
            $result = upsert_if_changed($mysqli, 'comet_matches', $keyCols, $data);

            if ($result['action'] === 'inserted') {
                $statsInserted++;
            } elseif ($result['action'] === 'updated') {
                $statsUpdated++;
            } else {
                $statsSkipped++;
            }

        } catch (Exception $e) {
            echo "⚠️  Error upserting match {$match['matchFifaId']}: " . $e->getMessage() . "\n";
            $errors++;
        }
    }

    $homeScore = $match['homeFinalResult'] ?? '-';
    $awayScore = $match['awayFinalResult'] ?? '-';
    echo "✅ Processed " . count($matches) . " matches\n";

    // Update competition flags
    $compId = $comp['competitionFifaId'];
    $playedCountComp = $mysqli->query("SELECT COUNT(*) as c FROM comet_matches WHERE competition_fifa_id = {$compId} AND match_status = 'played'")->fetch_assoc()['c'];
    $scheduledCountComp = $mysqli->query("SELECT COUNT(*) as c FROM comet_matches WHERE competition_fifa_id = {$compId} AND match_status = 'scheduled'")->fetch_assoc()['c'];
    $mysqli->query("UPDATE comet_club_competitions SET flag_played_matches = {$playedCountComp}, flag_scheduled_matches = {$scheduledCountComp} WHERE competitionFifaId = {$compId}");
    echo "   📊 Flags updated: {$playedCountComp} played, {$scheduledCountComp} scheduled\n\n";

    // Pause between competitions
    usleep(200000); // 200ms
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ COMET MATCHES SYNC COMPLETED\n\n";
echo "📊 Statistics:\n";
echo "   - Competitions processed: " . count($competitions) . "\n";
echo "   - Total matches: {$totalMatches}\n";
echo "   - Inserted: {$statsInserted}\n";
echo "   - Updated: {$statsUpdated}\n";
echo "   - Skipped (unchanged): {$statsSkipped}\n";
echo "   - Errors: {$errors}\n\n";

// Final count
$finalCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_matches")->fetch_assoc()['count'];
$playedCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_matches WHERE match_status = 'played'")->fetch_assoc()['count'];
$scheduledCount = $mysqli->query("SELECT COUNT(*) as count FROM comet_matches WHERE match_status = 'scheduled'")->fetch_assoc()['count'];

echo "📈 DATABASE STATUS:\n";
echo "   - Total matches: {$finalCount}\n";
echo "   - Played: {$playedCount}\n";
echo "   - Scheduled: {$scheduledCount}\n";

$mysqli->close();

?>
