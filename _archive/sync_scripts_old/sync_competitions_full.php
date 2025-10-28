<?php

require_once __DIR__ . '/lib/sync_helpers.php';

// SYNC COMPETITIONS WITH TEAMS
echo "⏱️  SYNC COMPETITIONS (with Teams & Logos)\n";
echo str_repeat("=", 60) . "\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'kpkb3');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$apiUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$teamFifaId = 598;

// Get active competitions
$competitionsQuery = "SELECT competitionFifaId, internationalName FROM comet_club_competitions WHERE status = 'active'";
$competitionsResult = $mysqli->query($competitionsQuery);

$competitions = [];
while ($row = $competitionsResult->fetch_assoc()) {
    $competitions[] = $row;
}

echo "📊 Active Competitions: " . count($competitions) . "\n\n";

$statsInserted = 0;
$statsUpdated = 0;
$statsSkipped = 0;

foreach ($competitions as $comp) {
    echo "📋 {$comp['internationalName']}\n";
    echo str_repeat('-', 60) . "\n";

    // Get competition details from API
    $ch = curl_init("{$apiUrl}/competition/{$comp['competitionFifaId']}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        echo "❌ HTTP {$httpCode}\n\n";
        continue;
    }

    $compData = json_decode($response, true);
    if (empty($compData)) {
        echo "⚠️  No data\n\n";
        continue;
    }

    // Get teams from matches
    $matchesQuery = "SELECT DISTINCT
                        team_fifa_id_home as team_id,
                        team_name_home as team_name
                     FROM comet_matches
                     WHERE competition_fifa_id = {$comp['competitionFifaId']}
                     AND team_fifa_id_home IS NOT NULL
                     UNION
                     SELECT DISTINCT
                        team_fifa_id_away as team_id,
                        team_name_away as team_name
                     FROM comet_matches
                     WHERE competition_fifa_id = {$comp['competitionFifaId']}
                     AND team_fifa_id_away IS NOT NULL
                     ORDER BY team_name";

    $teamsResult = $mysqli->query($matchesQuery);
    $teams = [];
    $teamNames = [];

    while ($team = $teamsResult->fetch_assoc()) {
        $teams[] = $team['team_id'];
        $teamNames[] = $team['team_name'];
    }

    // Build team list and logo paths
    $teamList = implode(', ', $teamNames);
    $logoPath = "storage/team-logos/{$comp['competitionFifaId']}";

    // Update competition with team info
    $keyCols = ['competitionFifaId' => $comp['competitionFifaId']];

    $data = [
        'competitionFifaId' => $comp['competitionFifaId'],
        'ageCategory' => $compData['ageCategory'] ?? null,
        'ageCategoryName' => $compData['ageCategoryName'] ?? null,
        'internationalName' => $compData['internationalName'] ?? $comp['internationalName'],
        'season' => $compData['season'] ?? null,
        'status' => 'active',
        'team_count' => count($teams),
        'team_list' => substr($teamList, 0, 500), // Limit to 500 chars
        'logo_path' => $logoPath
    ];

    // Get match counts
    $playedCount = $mysqli->query("SELECT COUNT(*) as c FROM comet_matches WHERE competition_fifa_id = {$comp['competitionFifaId']} AND match_status = 'played'")->fetch_assoc()['c'];
    $scheduledCount = $mysqli->query("SELECT COUNT(*) as c FROM comet_matches WHERE competition_fifa_id = {$comp['competitionFifaId']} AND match_status = 'scheduled'")->fetch_assoc()['c'];

    $data['flag_played_matches'] = $playedCount;
    $data['flag_scheduled_matches'] = $scheduledCount;

    try {
        $result = upsert_if_changed($mysqli, 'comet_club_competitions', $keyCols, $data);

        if ($result['action'] === 'inserted') {
            $statsInserted++;
        } elseif ($result['action'] === 'updated') {
            $statsUpdated++;
        } else {
            $statsSkipped++;
        }

        echo "✅ " . count($teams) . " teams | {$playedCount} played, {$scheduledCount} scheduled | {$result['action']}\n";

    } catch (Exception $e) {
        echo "⚠️  Error: " . $e->getMessage() . "\n";
    }

    echo "\n";
    usleep(200000);
}

echo str_repeat("=", 60) . "\n";
echo "✅ COMPETITIONS SYNC COMPLETED\n\n";
echo "📊 Statistics:\n";
echo "   - Competitions: " . count($competitions) . "\n";
echo "   - Inserted: {$statsInserted}\n";
echo "   - Updated: {$statsUpdated}\n";
echo "   - Skipped: {$statsSkipped}\n";

$mysqli->close();

?>
