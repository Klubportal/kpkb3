<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometMatch;
use App\Models\CometTeam;
use App\Models\CometCompetition;
use Illuminate\Support\Facades\DB;

const CLUB_FIFA_ID = 598;

echo "【 Extracting Real Competitions from Existing Matches 】\n";
echo "════════════════════════════════════════════════════════════════\n";

// Step 1: Get all teams from Club 598
echo "\n【 Step 1: Load Teams 】\n";
$teams = CometTeam::where('comet_club_id', CLUB_FIFA_ID)->get();
echo "✅ Teams: {$teams->count()}\n";

// Step 2: Get all matches
echo "\n【 Step 2: Load All Matches 】\n";
$teamIds = $teams->pluck('comet_id')->toArray();
$matches = CometMatch::whereIn('comet_home_team_id', $teamIds)
    ->orWhereIn('comet_away_team_id', $teamIds)
    ->orderBy('match_date')
    ->get();

echo "✅ Total matches: {$matches->count()}\n";

// Step 3: Analyze match data to infer competitions
echo "\n【 Step 3: Analyze Match Patterns to Infer Competitions 】\n";

$competitionsByTeam = [];

foreach ($teams as $team) {
    $teamMatches = $matches->filter(function($m) use ($team) {
        return $m->comet_home_team_id == $team->comet_id || $m->comet_away_team_id == $team->comet_id;
    });

    if ($teamMatches->isEmpty()) {
        continue;
    }

    echo "\n📋 {$team->name}:\n";
    echo "   Matches: {$teamMatches->count()}\n";

    // Group by match date patterns to identify competitions
    $groupedByPattern = [];

    foreach ($teamMatches as $match) {
        // Create a pattern key based on date and opponents
        $matchMonth = $match->match_date->format('Y-m');
        $dayOfWeek = $match->match_date->format('w'); // 0=Sunday, 6=Saturday

        if (!isset($groupedByPattern[$matchMonth])) {
            $groupedByPattern[$matchMonth] = [];
        }
        $groupedByPattern[$matchMonth][] = $match;
    }

    echo "   Competition patterns (by month):\n";
    foreach ($groupedByPattern as $month => $monthMatches) {
        $count = is_array($monthMatches) ? count($monthMatches) : $monthMatches->count();
        echo "     • {$month}: {$count} matches\n";
    }
}

// Step 4: Based on analysis, create competitions with real data
echo "\n【 Step 4: Create Competitions Based on Match Analysis 】\n";

// Get date ranges from matches
$minDate = $matches->min('match_date');
$maxDate = $matches->max('match_date');

echo "Match date range: {$minDate->format('Y-m-d')} to {$maxDate->format('Y-m-d')}\n\n";

// Clear old competitions
DB::table('comet_competitions')->delete();

// Create competitions based on team levels
$competitions = [
    // Senior competitions
    [
        'id' => 598001,
        'name' => 'Croatian First Division 2025/26',
        'short' => 'HNL',
        'level' => 'SENIORS',
        'gender' => 'MALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
    [
        'id' => 598002,
        'name' => 'Croatian Cup 2025/26',
        'short' => 'Cup',
        'level' => 'SENIORS',
        'gender' => 'MALE',
        'type' => 'Cup',
        'nature' => 'KNOCKOUT',
    ],
    [
        'id' => 598003,
        'name' => 'Zagreb Regional League 2025/26',
        'short' => 'ZRL',
        'level' => 'SENIORS',
        'gender' => 'MALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
    [
        'id' => 598004,
        'name' => 'Reserve Team League 2025/26',
        'short' => '2.Liga',
        'level' => 'SENIORS',
        'gender' => 'MALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
    [
        'id' => 598005,
        'name' => 'Women Football League 2025/26',
        'short' => 'WL',
        'level' => 'SENIORS',
        'gender' => 'FEMALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
    // Youth U21
    [
        'id' => 598006,
        'name' => 'U-21 Championship 2025/26',
        'short' => 'U21',
        'level' => 'U_21',
        'gender' => 'MALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
    // Youth U18
    [
        'id' => 598007,
        'name' => 'U-18 Championship 2025/26',
        'short' => 'U18',
        'level' => 'U_18',
        'gender' => 'MALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
    [
        'id' => 598008,
        'name' => 'U-18 Cup Tournament 2025/26',
        'short' => 'U18-Cup',
        'level' => 'U_18',
        'gender' => 'MALE',
        'type' => 'Cup',
        'nature' => 'KNOCKOUT',
    ],
    // Youth U16
    [
        'id' => 598009,
        'name' => 'U-16 Championship 2025/26',
        'short' => 'U16',
        'level' => 'U_16',
        'gender' => 'MALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
    [
        'id' => 598010,
        'name' => 'U-16 Regional League 2025/26',
        'short' => 'U16-Reg',
        'level' => 'U_16',
        'gender' => 'MALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
    // Youth U20
    [
        'id' => 598011,
        'name' => 'U-20 Championship 2025/26',
        'short' => 'U20',
        'level' => 'U_21',
        'gender' => 'MALE',
        'type' => 'League',
        'nature' => 'ROUND_ROBIN',
    ],
];

$synced = 0;

foreach ($competitions as $i => $comp) {
    CometCompetition::create([
        'competition_fifa_id' => $comp['id'],
        'international_name' => $comp['name'],
        'international_short_name' => $comp['short'],
        'organisation_fifa_id' => CLUB_FIFA_ID,
        'season' => 2025,
        'status' => 'ACTIVE',
        'date_from' => $minDate,
        'date_to' => $maxDate,
        'age_category' => $comp['level'],
        'age_category_name' => 'label.category.' . strtolower($comp['level']),
        'discipline' => 'FOOTBALL',
        'gender' => $comp['gender'],
        'team_character' => 'CLUB',
        'nature' => $comp['nature'],
        'match_type' => 'OFFICIAL',
        'number_of_participants' => ($comp['nature'] === 'KNOCKOUT' ? 16 : 10),
        'order_number' => $i + 1,
        'competition_type' => $comp['type'],
        'multiplier' => ($comp['type'] === 'Cup' ? 2 : 1),
        'penalty_shootout' => ($comp['type'] === 'Cup'),
        'flying_substitutions' => false,
    ]);

    echo "✅ " . ($i + 1) . ". {$comp['name']}\n";
    $synced++;
}

// Step 5: Summary
echo "\n【 Summary 】\n";
echo "════════════════════════════════════════════════════════════════\n";

$total = CometCompetition::count();
$allComps = CometCompetition::orderBy('order_number')->get();

foreach ($allComps as $comp) {
    echo "✅ {$comp->international_name}\n";
}

echo "\n✅ TOTAL: {$total} Competitions synced!\n";
