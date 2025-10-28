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
use App\Models\CometMatchStat;
use App\Models\CometPlayerStat;
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
        CURLOPT_TIMEOUT => 60,
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

echo "【 Syncing All Comet Data for Club 598 - Season 25/26 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Get all active competitions
$competitions = CometCompetition::where('status', 'ACTIVE')
    ->where('season', 2026)
    ->get();

echo "Found " . $competitions->count() . " active competitions\n\n";

$teamIds = [];
$clubIds = [];
$playerIds = [];
$matchIds = [];

// STEP 1: Sync Teams and Clubs
echo "【 STEP 1: Syncing Teams & Clubs 】\n";
foreach ($competitions as $comp) {
    echo "- Fetching teams for: {$comp->international_name}\n";

    $url = $baseUrl . '/api/export/comet/teams?competitionFifaId=' . $comp->competition_fifa_id;
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['teams'])) {
        foreach ($data['teams'] as $team) {
            // Sync Club
            $club = CometClub::updateOrCreate(
                ['comet_id' => $team['club']['fifaId']],
                [
                    'name' => $team['club']['name'] ?? null,
                    'international_name' => $team['club']['internationalName'] ?? null,
                    'country_code' => $team['club']['country'] ?? null,
                    'founded_year' => $team['club']['founded'] ?? null,
                ]
            );

            // Sync Team
            $teamModel = CometTeam::updateOrCreate(
                ['comet_id' => $team['fifaId']],
                [
                    'comet_club_id' => $club->comet_id,
                    'name' => $team['name'] ?? null,
                    'short_name' => $team['shortName'] ?? null,
                    'season' => $team['season'] ?? 2026,
                    'gender' => $team['gender'] ?? 'MALE',
                    'age_category' => $team['ageCategory'] ?? 'SENIORS',
                ]
            );

            $teamIds[$team['fifaId']] = $teamModel;
            $clubIds[$club->comet_id] = $club;
        }
    }
}

echo "Synced " . count(array_unique($teamIds)) . " unique teams\n";
echo "Synced " . count($clubIds) . " unique clubs\n\n";

// STEP 2: Sync Players
echo "【 STEP 2: Syncing Players 】\n";
foreach ($teamIds as $comet_team_id => $teamModel) {
    echo "- Fetching players for team ID: $comet_team_id\n";

    $url = $baseUrl . '/api/export/comet/teams/' . $comet_team_id . '/players';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['players'])) {
        foreach ($data['players'] as $player) {
            $playerModel = CometPlayer::updateOrCreate(
                ['comet_id' => $player['fifaId']],
                [
                    'comet_team_id' => $teamModel->comet_id,
                    'comet_club_id' => $teamModel->comet_club_id,
                    'shirt_number' => $player['shirtNumber'] ?? null,
                    'first_name' => $player['firstName'] ?? null,
                    'last_name' => $player['lastName'] ?? null,
                    'date_of_birth' => $player['dateOfBirth'] ?? null,
                    'position' => $player['position'] ?? 'FW',
                    'country_code' => $player['country'] ?? null,
                    'height' => $player['height'] ?? null,
                    'weight' => $player['weight'] ?? null,
                    'photo_url' => $player['photo'] ?? null,
                ]
            );

            $playerIds[$player['fifaId']] = $playerModel;
        }
    }
}

echo "Synced " . count($playerIds) . " unique players\n\n";

// STEP 3: Sync Matches
echo "【 STEP 3: Syncing Matches 】\n";
$totalMatches = 0;
foreach ($competitions as $comp) {
    echo "- Fetching matches for: {$comp->international_name}\n";

    $url = $baseUrl . '/api/export/comet/competitions/' . $comp->competition_fifa_id . '/matches';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['matches'])) {
        foreach ($data['matches'] as $match) {
            $matchModel = CometMatch::updateOrCreate(
                ['comet_id' => $match['fifaId']],
                [
                    'comet_home_team_id' => $match['homeTeam']['fifaId'] ?? null,
                    'comet_away_team_id' => $match['awayTeam']['fifaId'] ?? null,
                    'comet_home_club_id' => $match['homeTeam']['club']['fifaId'] ?? null,
                    'comet_away_club_id' => $match['awayTeam']['club']['fifaId'] ?? null,
                    'match_day' => $match['matchDay'] ?? null,
                    'match_date' => isset($match['date']) ? date('Y-m-d H:i:s', strtotime($match['date'])) : null,
                    'location' => $match['venue'] ?? null,
                    'home_goals' => $match['homeTeamGoals'] ?? null,
                    'away_goals' => $match['awayTeamGoals'] ?? null,
                    'status' => strtolower($match['status'] ?? 'scheduled'),
                    'referee' => $match['referee'] ?? null,
                    'spectators' => $match['spectators'] ?? null,
                ]
            );

            $matchIds[$match['fifaId']] = $matchModel;
            $totalMatches++;
        }
    }
}

echo "Synced $totalMatches matches\n\n";

// STEP 4: Sync Match Events (Goals, Cards, etc.)
echo "【 STEP 4: Syncing Match Events 】\n";
$totalEvents = 0;
foreach ($matchIds as $comet_match_id => $matchModel) {
    $url = $baseUrl . '/api/export/comet/matches/' . $comet_match_id . '/events';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['events'])) {
        foreach ($data['events'] as $event) {
            $eventType = strtolower($event['type'] ?? 'other');

            // Map event type
            $mappedType = match($eventType) {
                'goal' => 'goal',
                'assist' => 'assist',
                'yellowcard' => 'yellow_card',
                'redcard' => 'red_card',
                'substitutionin' => 'substitution_in',
                'substitutionout' => 'substitution_out',
                default => $eventType
            };

            $eventModel = CometMatchEvent::updateOrCreate(
                ['comet_id' => $event['fifaId']],
                [
                    'comet_match_id' => $matchModel->comet_id,
                    'comet_player_id' => $event['player']['fifaId'] ?? null,
                    'comet_team_id' => $event['team']['fifaId'] ?? null,
                    'event_type' => $mappedType,
                    'minute' => $event['minute'] ?? 0,
                    'second' => $event['second'] ?? 0,
                    'added_time_minute' => $event['addedTimeMinute'] ?? null,
                    'description' => $event['description'] ?? null,
                    'is_penalty' => $event['isPenalty'] ?? false,
                    'is_own_goal' => $event['isOwnGoal'] ?? false,
                ]
            );

            $totalEvents++;
        }
    }
}

echo "Synced $totalEvents match events\n\n";

// STEP 5: Sync Player Statistics
echo "【 STEP 5: Syncing Player Statistics 】\n";
$totalStats = 0;
foreach ($playerIds as $comet_player_id => $playerModel) {
    $url = $baseUrl . '/api/export/comet/players/' . $comet_player_id . '/stats';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['playerStats'])) {
        foreach ($data['playerStats'] as $stat) {
            $statsModel = CometPlayerStat::updateOrCreate(
                ['comet_id' => $stat['fifaId']],
                [
                    'comet_player_id' => $playerModel->comet_id,
                    'comet_team_id' => $playerModel->comet_team_id,
                    'comet_season_id' => $stat['seasonId'] ?? null,
                    'matches_played' => $stat['matchesPlayed'] ?? 0,
                    'goals' => $stat['goals'] ?? 0,
                    'assists' => $stat['assists'] ?? 0,
                    'yellow_cards' => $stat['yellowCards'] ?? 0,
                    'red_cards' => $stat['redCards'] ?? 0,
                    'average_rating' => $stat['averageRating'] ?? null,
                    'minutes_played' => $stat['minutesPlayed'] ?? 0,
                    'own_goals' => $stat['ownGoals'] ?? 0,
                    'penalties_scored' => $stat['penaltiesScored'] ?? 0,
                ]
            );

            $totalStats++;
        }
    }
}

echo "Synced $totalStats player statistics\n\n";

// STEP 6: Sync Match Statistics
echo "【 STEP 6: Syncing Match Statistics 】\n";
$totalMatchStats = 0;
foreach ($matchIds as $comet_match_id => $matchModel) {
    $url = $baseUrl . '/api/export/comet/matches/' . $comet_match_id . '/stats';
    $data = makeApiCall($url, $headers);

    if ($data && isset($data['stats'])) {
        foreach ($data['stats'] as $stat) {
            $matchStatsModel = CometMatchStat::updateOrCreate(
                ['comet_id' => $stat['fifaId']],
                [
                    'comet_match_id' => $matchModel->comet_id,
                    'comet_team_id' => $stat['team']['fifaId'] ?? null,
                    'shots' => $stat['shots'] ?? 0,
                    'shots_on_goal' => $stat['shotsOnGoal'] ?? 0,
                    'possession' => $stat['possession'] ?? 0,
                    'passes' => $stat['passes'] ?? 0,
                    'pass_accuracy' => $stat['passAccuracy'] ?? 0,
                    'fouls' => $stat['fouls'] ?? 0,
                    'offsides' => $stat['offsides'] ?? 0,
                    'corner_kicks' => $stat['cornerKicks'] ?? 0,
                    'throw_ins' => $stat['throwIns'] ?? 0,
                    'goalkeeper_saves' => $stat['goalkeeperSaves'] ?? 0,
                ]
            );

            $totalMatchStats++;
        }
    }
}

echo "Synced $totalMatchStats match statistics\n\n";

echo "════════════════════════════════════════════════════════════════\n";
echo "✅ All Data Synced Successfully!\n";
echo "════════════════════════════════════════════════════════════════\n\n";
echo "Summary:\n";
echo "  - Clubs: " . count($clubIds) . "\n";
echo "  - Teams: " . count(array_unique($teamIds)) . "\n";
echo "  - Players: " . count($playerIds) . "\n";
echo "  - Matches: $totalMatches\n";
echo "  - Match Events: $totalEvents\n";
echo "  - Player Stats: $totalStats\n";
echo "  - Match Stats: $totalMatchStats\n";
