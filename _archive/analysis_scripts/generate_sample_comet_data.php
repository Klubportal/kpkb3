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
use Illuminate\Support\Facades\DB;

echo "【 API Limitation Assessment 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

echo "API Endpoints Available:\n";
echo "✅ /api/export/comet/competitions - WORKING\n";
echo "❌ /api/export/comet/teams - 404 NOT FOUND\n";
echo "❌ /api/export/comet/matches - 404 NOT FOUND\n";
echo "❌ /api/export/comet/players - 404 NOT FOUND\n";
echo "❌ /api/export/comet/matches/{id}/events - 404 NOT FOUND\n";
echo "❌ /api/export/comet/players/{id}/stats - 404 NOT FOUND\n\n";

echo "【 Alternative Solution: Generate Sample Data 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

echo "Since the Comet API only provides competitions data, we'll create\n";
echo "realistic sample data for demonstration and testing purposes.\n\n";

// Get competitions
$competitions = CometCompetition::where('status', 'ACTIVE')
    ->where('season', 2026)
    ->get();

echo "Step 1: Creating Teams & Clubs for " . $competitions->count() . " competitions\n";

$teams = [];
$clubs = [];

// Sample teams for NK Prigorje and opponents
$sampleTeams = [
    ['name' => 'NK Prigorje', 'short' => 'PRI'],
    ['name' => 'NK Zagreb', 'short' => 'ZAG'],
    ['name' => 'NK Dinamo', 'short' => 'DIN'],
    ['name' => 'NK Hajduk', 'short' => 'HAJ'],
    ['name' => 'NK Rijeka', 'short' => 'RIJ'],
    ['name' => 'NK Osijek', 'short' => 'OSI'],
    ['name' => 'NK Slaven', 'short' => 'SLA'],
    ['name' => 'NK Lokomotiva', 'short' => 'LOK'],
];

foreach ($sampleTeams as $idx => $teamData) {
    // Create club first
    $club = CometClub::firstOrCreate(
        ['comet_id' => 1000 + $idx],
        [
            'name' => $teamData['name'],
            'international_name' => $teamData['name'],
            'country_code' => 'HR',
            'founded_year' => 1990 + $idx,
        ]
    );

    $clubs[$club->comet_id] = $club;

    // Create team
    $team = CometTeam::firstOrCreate(
        ['comet_id' => 2000 + $idx],
        [
            'comet_club_id' => $club->comet_id,
            'name' => $teamData['name'],
            'short_name' => $teamData['short'],
            'season' => 2026,
            'gender' => 'MALE',
            'age_category' => 'SENIORS',
        ]
    );

    $teams[$team->comet_id] = $team;
}

echo "✅ Created " . count($teams) . " teams and " . count($clubs) . " clubs\n\n";

// Step 2: Create Players
echo "Step 2: Creating Sample Players\n";

$players = [];
$samplePlayers = [
    ['name' => 'Marko Miličević', 'pos' => 'FW', 'num' => 9],
    ['name' => 'Luka Bogdanović', 'pos' => 'FW', 'num' => 10],
    ['name' => 'Igor Barišić', 'pos' => 'MID', 'num' => 8],
    ['name' => 'Dejan Savić', 'pos' => 'DEF', 'num' => 4],
    ['name' => 'Vladimir Stojković', 'pos' => 'GK', 'num' => 1],
    ['name' => 'Filip Nevistić', 'pos' => 'FW', 'num' => 11],
    ['name' => 'Tomislav Dujmović', 'pos' => 'MID', 'num' => 6],
    ['name' => 'Marinović Petar', 'pos' => 'DEF', 'num' => 5],
];

$playerIdx = 0;
foreach ($teams as $team_comet_id => $team) {
    foreach ($samplePlayers as $idx => $playerData) {
        $player = CometPlayer::firstOrCreate(
            ['comet_id' => 3000 + $playerIdx],
            [
                'comet_team_id' => $team->comet_id,
                'comet_club_id' => $team->comet_club_id,
                'shirt_number' => $playerData['num'],
                'first_name' => explode(' ', $playerData['name'])[0],
                'last_name' => explode(' ', $playerData['name'])[1] ?? 'Unknown',
                'date_of_birth' => '1995-05-15',
                'position' => $playerData['pos'],
                'country_code' => 'HR',
                'height' => 180 + rand(-10, 10),
                'weight' => 75 + rand(-10, 15),
            ]
        );

        $players[$player->comet_id] = $player;
        $playerIdx++;
    }
}

echo "✅ Created " . count($players) . " players\n\n";

// Step 3: Create sample matches
echo "Step 3: Creating Sample Matches\n";

$matches = [];
$matchIdx = 0;

$teamArray = array_values($teams);
for ($i = 0; $i < count($teamArray) - 1; $i += 2) {
    $homeTeam = $teamArray[$i];
    $awayTeam = $teamArray[$i + 1];

    $match = CometMatch::firstOrCreate(
        ['comet_id' => 4000 + $matchIdx],
        [
            'comet_home_team_id' => $homeTeam->comet_id,
            'comet_away_team_id' => $awayTeam->comet_id,
            'comet_home_club_id' => $homeTeam->comet_club_id,
            'comet_away_club_id' => $awayTeam->comet_club_id,
            'match_day' => '1',
            'match_date' => now()->addDays(rand(1, 30)),
            'location' => 'Gradski stadion',
            'home_goals' => rand(0, 4),
            'away_goals' => rand(0, 4),
            'status' => 'finished',
            'referee' => 'Referee ' . (rand(1, 10)),
            'spectators' => rand(500, 5000),
        ]
    );

    $matches[$match->comet_id] = $match;
    $matchIdx++;
}

echo "✅ Created " . count($matches) . " matches\n\n";

// Step 4: Create match events (goals)
echo "Step 4: Creating Match Events (Goals & Cards)\n";

$eventIdx = 0;
$totalGoals = 0;

foreach ($matches as $match) {
    // Get players from home and away teams
    $homeTeamPlayers = CometPlayer::where('comet_team_id', $match->comet_home_team_id)
        ->where('position', '!=', 'GK')
        ->get();

    $awayTeamPlayers = CometPlayer::where('comet_team_id', $match->comet_away_team_id)
        ->where('position', '!=', 'GK')
        ->get();

    // Add home team goals
    $homeGoals = $match->home_goals ?? 0;
    for ($i = 0; $i < $homeGoals; $i++) {
        $player = $homeTeamPlayers->random();
        $event = CometMatchEvent::firstOrCreate(
            ['comet_id' => 5000 + $eventIdx],
            [
                'comet_match_id' => $match->comet_id,
                'comet_player_id' => $player->comet_id,
                'comet_team_id' => $match->comet_home_team_id,
                'event_type' => 'goal',
                'minute' => rand(1, 90),
                'second' => rand(0, 59),
                'is_penalty' => rand(0, 1) === 1 && rand(0, 2) === 0 ? true : false,
                'is_own_goal' => false,
            ]
        );
        $totalGoals++;
        $eventIdx++;
    }

    // Add away team goals
    $awayGoals = $match->away_goals ?? 0;
    for ($i = 0; $i < $awayGoals; $i++) {
        $player = $awayTeamPlayers->random();
        $event = CometMatchEvent::firstOrCreate(
            ['comet_id' => 5000 + $eventIdx],
            [
                'comet_match_id' => $match->comet_id,
                'comet_player_id' => $player->comet_id,
                'comet_team_id' => $match->comet_away_team_id,
                'event_type' => 'goal',
                'minute' => rand(1, 90),
                'second' => rand(0, 59),
                'is_penalty' => rand(0, 1) === 1 && rand(0, 2) === 0 ? true : false,
                'is_own_goal' => false,
            ]
        );
        $totalGoals++;
        $eventIdx++;
    }

    // Add some cards
    for ($i = 0; $i < rand(1, 4); $i++) {
        $allPlayers = CometPlayer::whereIn('comet_id',
            array_merge(
                $homeTeamPlayers->pluck('comet_id')->toArray(),
                $awayTeamPlayers->pluck('comet_id')->toArray()
            )
        )->get();

        $player = $allPlayers->random();
        $event = CometMatchEvent::create([
            'comet_id' => 5000 + $eventIdx,
            'comet_match_id' => $match->comet_id,
            'comet_player_id' => $player->comet_id,
            'comet_team_id' => $player->comet_team_id,
            'event_type' => rand(0, 1) === 0 ? 'yellow_card' : 'red_card',
            'minute' => rand(1, 90),
            'second' => rand(0, 59),
        ]);
        $eventIdx++;
    }
}

echo "✅ Created " . $eventIdx . " match events (" . $totalGoals . " goals)\n\n";

// Step 5: Create player statistics
echo "Step 5: Creating Player Statistics\n";

$statsIdx = 0;
foreach ($players as $player) {
    $goals = CometMatchEvent::where('comet_player_id', $player->comet_id)
        ->where('event_type', 'goal')
        ->count();

    $yellowCards = CometMatchEvent::where('comet_player_id', $player->comet_id)
        ->where('event_type', 'yellow_card')
        ->count();

    $redCards = CometMatchEvent::where('comet_player_id', $player->comet_id)
        ->where('event_type', 'red_card')
        ->count();

    $stat = CometPlayerStat::firstOrCreate(
        ['comet_id' => 6000 + $statsIdx],
        [
            'comet_player_id' => $player->comet_id,
            'comet_team_id' => $player->comet_team_id,
            'matches_played' => rand(5, 15),
            'goals' => $goals,
            'assists' => rand(0, 5),
            'yellow_cards' => $yellowCards,
            'red_cards' => $redCards,
            'average_rating' => round(6.0 + rand(0, 30) / 10, 1),
            'minutes_played' => rand(300, 1350),
            'penalties_scored' => rand(0, 2),
        ]
    );

    $statsIdx++;
}

echo "✅ Created " . $statsIdx . " player statistics\n\n";

echo "════════════════════════════════════════════════════════════════\n";
echo "✅ Sample Data Generation Complete!\n";
echo "════════════════════════════════════════════════════════════════\n\n";

echo "Summary:\n";
echo "  - Clubs: " . count($clubs) . "\n";
echo "  - Teams: " . count($teams) . "\n";
echo "  - Players: " . count($players) . "\n";
echo "  - Matches: " . count($matches) . "\n";
echo "  - Match Events: " . $eventIdx . "\n";
echo "  - Player Statistics: " . $statsIdx . "\n";
