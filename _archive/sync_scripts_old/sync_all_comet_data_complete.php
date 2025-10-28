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
use App\Models\TopScorer;
use Illuminate\Support\Facades\DB;

$baseUrl = 'https://api-hns.analyticom.de';
$username = 'nkprigorje';
$password = '3c6nR$dS';
$auth = base64_encode("$username:$password");

$headers = [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/json'
];

function makeApiCall($url, $headers) {
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
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }

    return null;
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  Comet Data Sync & Top Scorers Calculation - Production API    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Get active competitions
$competitions = CometCompetition::where('status', 'ACTIVE')
    ->where('season', 2026)
    ->get();

echo "【 Step 1/5: Creating Clubs & Teams 】\n";
echo "Found " . $competitions->count() . " active competitions\n\n";

$teams = [];
$clubs = [];

foreach ($competitions as $comp) {
    echo "→ {$comp->international_name}\n";

    $url = $baseUrl . '/clubs?competition=' . $comp->competition_fifa_id . '&limit=100';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['data'])) {
        echo "  ✓ Found " . count($data['data']) . " teams\n";

        foreach ($data['data'] as $clubData) {
            // Create Club
            $club = CometClub::updateOrCreate(
                ['comet_id' => $clubData['fifaId']],
                [
                    'name' => $clubData['name'] ?? null,
                    'international_name' => $clubData['name'] ?? null,
                    'country_code' => $clubData['countryCode'] ?? null,
                    'founded_year' => $clubData['founded'] ?? null,
                ]
            );

            // Create Team
            $team = CometTeam::updateOrCreate(
                ['comet_id' => $clubData['fifaId']],
                [
                    'comet_club_id' => $club->comet_id,
                    'name' => $clubData['name'] ?? null,
                    'short_name' => $clubData['shortName'] ?? null,
                    'season' => 2026,
                    'gender' => 'MALE',
                    'age_category' => 'SENIORS',
                ]
            );

            $teams[$team->comet_id] = $team;
            $clubs[$club->comet_id] = $club;
        }
    }
}

echo "\nClubs: " . count($clubs) . " | Teams: " . count($teams) . "\n\n";

// Step 2: Sync Matches
echo "【 Step 2/5: Syncing Matches 】\n";

$totalMatches = 0;
foreach ($competitions as $comp) {
    echo "→ {$comp->international_name}\n";

    $url = $baseUrl . '/matches?competition=' . $comp->competition_fifa_id . '&limit=100&status=FINISHED';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['data'])) {
        echo "  ✓ Found " . count($data['data']) . " finished matches\n";

        foreach ($data['data'] as $match) {
            CometMatch::updateOrCreate(
                ['comet_id' => $match['fifaId']],
                [
                    'comet_home_team_id' => $match['homeClub']['fifaId'] ?? null,
                    'comet_away_team_id' => $match['awayClub']['fifaId'] ?? null,
                    'comet_home_club_id' => $match['homeClub']['fifaId'] ?? null,
                    'comet_away_club_id' => $match['awayClub']['fifaId'] ?? null,
                    'match_day' => $match['competition']['round'] ?? null,
                    'match_date' => $match['matchDate'] ?? null,
                    'location' => $match['stadium']['name'] ?? null,
                    'home_goals' => $match['score']['homeTeam'] ?? null,
                    'away_goals' => $match['score']['awayTeam'] ?? null,
                    'status' => strtolower($match['status'] ?? 'finished'),
                    'referee' => ($match['referee']['firstName'] ?? '') . ' ' . ($match['referee']['lastName'] ?? ''),
                    'spectators' => $match['attendance'] ?? null,
                ]
            );

            $totalMatches++;
        }
    }
}

echo "\nMatches synced: $totalMatches\n\n";

// Step 3: Sync Players
echo "【 Step 3/5: Syncing Players 】\n";

$totalPlayers = 0;
foreach ($competitions as $comp) {
    echo "→ {$comp->international_name}\n";

    $url = $baseUrl . '/players?competition=' . $comp->competition_fifa_id . '&limit=100';
    $page = 1;

    do {
        $pageUrl = $url . '&page=' . $page;
        $data = makeApiCall($pageUrl, $headers);

        if ($data && isset($data['data'])) {
            echo "  ✓ Page $page: " . count($data['data']) . " players\n";

            foreach ($data['data'] as $player) {
                CometPlayer::updateOrCreate(
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

echo "\nPlayers synced: $totalPlayers\n\n";

// Step 4: Sync Match Events (Goals, Cards, Subs)
echo "【 Step 4/5: Syncing Match Events (Goals, Cards, Etc.) 】\n";

$totalEvents = 0;
$goalsByPlayer = [];

$matches = CometMatch::all();
echo "Processing " . count($matches) . " matches for events...\n";

foreach ($matches as $idx => $match) {
    if ($idx % 5 === 0) {
        echo "  ✓ Processed " . $idx . " matches\n";
    }

    $url = $baseUrl . '/match/' . $match->comet_id . '/latest/events?seconds=9999999';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['events'])) {
        foreach ($data['events'] as $event) {
            // Skip deleted events
            if ($event['eventType'] === null) {
                continue;
            }

            $eventType = strtolower($event['eventType'] ?? 'other');

            // Track goals for top scorers
            if (in_array($eventType, ['goal', 'penalty_goal'])) {
                $playerId = $event['playerFifaId'];
                if (!isset($goalsByPlayer[$playerId])) {
                    $goalsByPlayer[$playerId] = [
                        'goals' => 0,
                        'player_data' => null,
                        'team_data' => null,
                    ];
                }
                $goalsByPlayer[$playerId]['goals']++;
            }

            CometMatchEvent::updateOrCreate(
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

echo "  ✓ Processed all matches\n";
echo "\nMatch events synced: $totalEvents\n";
echo "Unique goal scorers: " . count($goalsByPlayer) . "\n\n";

// Step 5: Calculate & Store Top Scorers
echo "【 Step 5/5: Calculating Top Scorers 】\n";

// Clear existing top scorers
TopScorer::truncate();

$scorerRank = 1;
$topScorerGoals = 0;

// Sort by goals descending
uasort($goalsByPlayer, function($a, $b) {
    return $b['goals'] - $a['goals'];
});

foreach ($goalsByPlayer as $playerId => $scoreData) {
    $player = CometPlayer::find($playerId);

    if (!$player) {
        continue;
    }

    $isLeading = false;
    if ($scorerRank === 1) {
        $topScorerGoals = $scoreData['goals'];
        $isLeading = true;
    }

    // Get competition from team
    $competition = CometCompetition::where('status', 'ACTIVE')
        ->where('season', 2026)
        ->first();

    TopScorer::create([
        'comet_id' => $playerId . '_' . ($competition->competition_fifa_id ?? 0),
        'comet_competition_id' => $competition->competition_fifa_id ?? 0,
        'comet_player_id' => $player->comet_id,
        'comet_team_id' => $player->comet_team_id ?? 0,
        'player_name' => ($player->first_name ?? '') . ' ' . ($player->last_name ?? ''),
        'team_name' => $player->team?->name ?? 'Unknown',
        'rank' => $scorerRank,
        'goals' => $scoreData['goals'],
        'assists' => 0,
        'matches_played' => 0,
        'goals_per_match' => 0,
        'is_leading_scorer' => $isLeading,
    ]);

    $scorerRank++;
}

echo "✓ Stored " . count($goalsByPlayer) . " top scorers\n\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  ✅ SYNC COMPLETE!                             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 Summary:\n";
echo "   Competitions: " . $competitions->count() . "\n";
echo "   Clubs: " . count($clubs) . "\n";
echo "   Teams: " . count($teams) . "\n";
echo "   Players: $totalPlayers\n";
echo "   Matches: $totalMatches\n";
echo "   Match Events: $totalEvents\n";
echo "   Top Scorers: " . count($goalsByPlayer) . "\n";
