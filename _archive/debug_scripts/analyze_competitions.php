<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometClub;
use App\Models\CometTeam;
use App\Models\CometMatch;
use Illuminate\Support\Facades\DB;

const CLUB_FIFA_ID = 598;

echo "【 Analyzing Competitions from Actual Match Data 】\n";
echo "════════════════════════════════════════════════════════════════\n";

// Step 1: Get all teams from Club 598
echo "\n【 Step 1: Load All Teams from Club 598 】\n";
$teams = CometTeam::where('comet_club_id', CLUB_FIFA_ID)->get();
echo "✅ Teams found: {$teams->count()}\n";

foreach ($teams as $team) {
    echo "   • {$team->name} (ID: {$team->comet_id})\n";
}

// Step 2: Get all matches for these teams
echo "\n【 Step 2: Get All Matches for These Teams 】\n";
$teamIds = $teams->pluck('comet_id')->toArray();

$matches = CometMatch::whereIn('comet_home_team_id', $teamIds)
    ->orWhereIn('comet_away_team_id', $teamIds)
    ->get();

echo "✅ Total matches: {$matches->count()}\n";

// Step 3: Extract unique competitions from matches
echo "\n【 Step 3: Extract Unique Competitions from Matches 】\n";

// Since matches don't have competition_id, we need to look at other patterns
// Let's group by team and see what competitions we can infer

$competitionData = [];
$matchesByTeam = [];

foreach ($matches as $match) {
    $homeTeam = $match->comet_home_team_id;
    $awayTeam = $match->comet_away_team_id;
    $matchDate = $match->match_date;

    // Store matches by team
    if (!isset($matchesByTeam[$homeTeam])) {
        $matchesByTeam[$homeTeam] = [];
    }
    if (!isset($matchesByTeam[$awayTeam])) {
        $matchesByTeam[$awayTeam] = [];
    }

    $matchesByTeam[$homeTeam][] = $match;
    $matchesByTeam[$awayTeam][] = $match;
}

// Step 4: List all teams and their matches
echo "\n【 Step 4: Teams and Their Match Distribution 】\n";

foreach ($teams as $team) {
    $teamMatchCount = isset($matchesByTeam[$team->comet_id]) ? count($matchesByTeam[$team->comet_id]) : 0;
    echo "\n📌 {$team->name}\n";
    echo "   Matches: {$teamMatchCount}\n";

    if (isset($matchesByTeam[$team->comet_id])) {
        // Get date range
        $teamMatches = $matchesByTeam[$team->comet_id];
        $dates = array_map(function($m) { return $m->match_date; }, $teamMatches);
        usort($dates, function($a, $b) { return strtotime($a) - strtotime($b); });

        echo "   Date range: {$dates[0]} to {$dates[count($dates)-1]}\n";

        // Count matches by day of week or patterns
        echo "   Opponents: " . count(array_unique(array_merge(
            array_map(fn($m) => $m->comet_home_team_id, $teamMatches),
            array_map(fn($m) => $m->comet_away_team_id, $teamMatches)
        ))) . " different teams\n";
    }
}

// Step 5: Try to infer competitions
echo "\n【 Step 5: Infer Competitions from Team Activity Patterns 】\n";

$competitionsByTeam = [];

foreach ($teams as $team) {
    if (!isset($matchesByTeam[$team->comet_id])) {
        continue;
    }

    $teamMatches = $matchesByTeam[$team->comet_id];

    // Group by rough date ranges to identify competitions
    $groupedByDate = [];
    foreach ($teamMatches as $match) {
        $month = date('Y-m', strtotime($match->match_date));
        if (!isset($groupedByDate[$month])) {
            $groupedByDate[$month] = [];
        }
        $groupedByDate[$month][] = $match;
    }

    echo "\n📋 {$team->name} - Competition patterns:\n";
    foreach ($groupedByDate as $month => $monthMatches) {
        echo "   {$month}: " . count($monthMatches) . " matches\n";
    }
}

// Step 6: Database check
echo "\n【 Step 6: Check Current Competitions in DB 】\n";

$allComps = DB::table('comet_competitions')->get();
echo "✅ Total competitions in DB: {$allComps->count()}\n";

foreach ($allComps as $comp) {
    echo "   • {$comp->international_name}\n";
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "💡 RECOMMENDATION: Competitions should be extracted from actual matches\n";
echo "   OR from Comet API if available with team participation data\n";
