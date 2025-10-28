<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;
use App\Models\CometTeam;
use App\Models\CometClub;
use App\Models\CometPlayer;
use App\Models\CometMatch;
use App\Models\CometMatchEvent;
use App\Models\CometPlayerStat;
use App\Models\CometMatchStat;

$baseUrl = 'https://api-hns.analyticom.de';  // Correct base URL
$username = 'nkprigorje';
$password = '3c6nR$dS';
$auth = base64_encode("$username:$password");

$headers = [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/json'
];

function makeApiCall($url, $headers) {
    echo "  ➜ GET $url\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }

    if ($httpCode !== 200) {
        echo "    Status: $httpCode\n";
        if ($error) {
            echo "    Error: $error\n";
        }
    }

    return null;
}

echo "【 Syncing All Comet Data from PRODUCTION API 】\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "Base URL: $baseUrl\n";
echo "Using documented endpoints from COMET_API_ENDPOINTS.md\n\n";

// Get all active competitions
$competitions = CometCompetition::where('status', 'ACTIVE')
    ->where('season', 2026)
    ->get();

echo "【 Step 1: Syncing Teams/Clubs 】\n";
echo "Found " . $competitions->count() . " active competitions\n\n";

$teams = [];
$clubs = [];
$teamCount = 0;
$clubCount = 0;

foreach ($competitions as $comp) {
    echo "Processing: {$comp->international_name}\n";
    echo "  Endpoint: /clubs?competition={$comp->competition_fifa_id}&limit=50\n";

    $url = $baseUrl . '/clubs?competition=' . $comp->competition_fifa_id . '&limit=50';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['data'])) {
        echo "  Found " . count($data['data']) . " teams\n";

        foreach ($data['data'] as $clubData) {
            // Store for later (we need to create club first)
            $clubs[$clubData['fifaId']] = $clubData;
            $teamCount++;
        }
    } else {
        echo "  ⚠ No data returned\n";
    }
    echo "\n";
}

echo "Total teams found: $teamCount\n\n";

// Now sync clubs first
echo "【 Step 2: Syncing Club Records 】\n";

foreach ($clubs as $fifaId => $clubData) {
    $club = CometClub::updateOrCreate(
        ['comet_id' => $fifaId],
        [
            'name' => $clubData['name'] ?? null,
            'international_name' => $clubData['name'] ?? null,
            'country_code' => $clubData['countryCode'] ?? null,
            'founded_year' => $clubData['founded'] ?? null,
        ]
    );
    $clubCount++;
}

echo "✅ Synced $clubCount clubs\n\n";

// Step 2: Sync Matches
echo "【 Step 3: Syncing Matches 】\n";

$totalMatches = 0;
foreach ($competitions as $comp) {
    echo "Fetching matches for: {$comp->international_name}\n";
    $url = $baseUrl . '/matches?competition=' . $comp->competition_fifa_id . '&limit=100';

    $page = 1;
    do {
        $pageUrl = $url . '&page=' . $page;
        echo "  Page $page: $pageUrl\n";

        $data = makeApiCall($pageUrl, $headers);

        if ($data && isset($data['data'])) {
            foreach ($data['data'] as $match) {
                $matchModel = CometMatch::updateOrCreate(
                    ['comet_id' => $match['fifaId']],
                    [
                        'comet_home_team_id' => $match['homeClub']['fifaId'] ?? null,
                        'comet_away_team_id' => $match['awayClub']['fifaId'] ?? null,
                        'comet_home_club_id' => $match['homeClub']['fifaId'] ?? null,
                        'comet_away_club_id' => $match['awayClub']['fifaId'] ?? null,
                        'match_day' => $match['competition']['round'] ?? null,
                        'match_date' => isset($match['matchDate']) ? $match['matchDate'] : null,
                        'location' => $match['stadium']['name'] ?? null,
                        'home_goals' => $match['score']['homeTeam'] ?? null,
                        'away_goals' => $match['score']['awayTeam'] ?? null,
                        'status' => strtolower($match['status'] ?? 'scheduled'),
                        'referee' => $match['referee']['firstName'] . ' ' . ($match['referee']['lastName'] ?? '') ?? null,
                        'spectators' => $match['attendance'] ?? null,
                    ]
                );

                $totalMatches++;
            }

            // Check if there are more pages
            $pagination = $data['pagination'] ?? [];
            if ($page >= ($pagination['pages'] ?? 1)) {
                break;
            }
            $page++;
        } else {
            break;
        }
    } while (true);

    echo "  ✅ Synced " . $totalMatches . " total matches\n\n";
}

// Step 3: Sync Players
echo "【 Step 4: Syncing Players 】\n";

$totalPlayers = 0;
foreach ($competitions as $comp) {
    echo "Fetching players for: {$comp->international_name}\n";
    $url = $baseUrl . '/players?competition=' . $comp->competition_fifa_id . '&limit=50';

    $page = 1;
    do {
        $pageUrl = $url . '&page=' . $page;
        echo "  Page $page...\n";

        $data = makeApiCall($pageUrl, $headers);

        if ($data && isset($data['data'])) {
            foreach ($data['data'] as $player) {
                $playerModel = CometPlayer::updateOrCreate(
                    ['comet_id' => $player['fifaId']],
                    [
                        'comet_team_id' => $player['club']['fifaId'] ?? null,
                        'comet_club_id' => $player['club']['fifaId'] ?? null,
                        'shirt_number' => $player['shirtNumber'] ?? null,
                        'first_name' => $player['firstName'] ?? null,
                        'last_name' => $player['lastName'] ?? null,
                        'date_of_birth' => $player['dateOfBirth'] ?? null,
                        'position' => $player['position'] ?? 'FW',
                        'country_code' => $player['nationalityCode'] ?? null,
                        'height' => $player['height'] ?? null,
                        'weight' => $player['weight'] ?? null,
                    ]
                );

                $totalPlayers++;
            }

            $pagination = $data['pagination'] ?? [];
            if ($page >= ($pagination['pages'] ?? 1)) {
                break;
            }
            $page++;
        } else {
            break;
        }
    } while (true);
}

echo "✅ Synced $totalPlayers total players\n\n";

// Step 4: Sync Match Events (Goals, Cards, etc.)
echo "【 Step 5: Syncing Match Events (Goals, Cards, Subs) 】\n";

$totalEvents = 0;
$matches = CometMatch::all();
echo "Processing " . count($matches) . " matches for events...\n";

foreach ($matches as $match) {
    $url = $baseUrl . '/match/' . $match->comet_id . '/latest/events?seconds=999999';

    $data = makeApiCall($url, $headers);

    if ($data && isset($data['events'])) {
        foreach ($data['events'] as $event) {
            // Skip deleted events (all NULL payload)
            if ($event['eventType'] === null) {
                continue;
            }

            $eventType = strtolower($event['eventType'] ?? 'other');

            $eventModel = CometMatchEvent::updateOrCreate(
                ['comet_id' => $event['id']],
                [
                    'comet_match_id' => $match->comet_id,
                    'comet_player_id' => $event['playerFifaId'] ?? null,
                    'comet_team_id' => $event['personName'] ?? null,
                    'event_type' => $eventType,
                    'minute' => $event['minute'] ?? 0,
                    'second' => $event['second'] ?? 0,
                    'is_penalty' => $eventType === 'penalty_goal',
                    'is_own_goal' => $eventType === 'own_goal',
                ]
            );

            $totalEvents++;
        }
    }
}

echo "✅ Synced $totalEvents match events\n\n";

echo "════════════════════════════════════════════════════════════════\n";
echo "✅ Complete Data Sync Finished!\n";
echo "════════════════════════════════════════════════════════════════\n\n";
echo "Summary:\n";
echo "  - Competitions: " . $competitions->count() . "\n";
echo "  - Clubs: $clubCount\n";
echo "  - Teams: $teamCount\n";
echo "  - Players: $totalPlayers\n";
echo "  - Matches: $totalMatches\n";
echo "  - Match Events: $totalEvents\n\n";

echo "Ready to calculate top scorers from goals!\n";
