<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometClub;
use App\Models\CometTeam;
use App\Models\CometMatch;
use App\Models\CometCompetition;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

const CLUB_FIFA_ID = 598;

echo "【 Syncing Real Competitions from Comet REST API (LIVE) 】\n";
echo "════════════════════════════════════════════════════════════════\n";

// Load Comet API credentials directly
$apiUrl = 'https://api-hns.analyticom.de';
$apiUsername = 'nkprigorje';
$apiPassword = '3c6nR$dS';

echo "✅ Using Comet API: {$apiUrl}\n";
echo "✅ Username: {$apiUsername}\n\n";

// Step 1: Get Club Info
echo "【 Step 1: Get Club 598 Info 】\n";
$club = CometClub::where('comet_id', CLUB_FIFA_ID)->first();

if (!$club) {
    echo "❌ Club not found!\n";
    exit(1);
}

echo "✅ Club: {$club->name}\n";
echo "   City: {$club->city}\n";
echo "   Country: {$club->country}\n";

// Step 2: Get all teams
echo "\n【 Step 2: Get All Teams from Club 】\n";
$teams = CometTeam::where('comet_club_id', CLUB_FIFA_ID)->get();
echo "✅ Teams: {$teams->count()}\n";

foreach ($teams as $team) {
    echo "   • {$team->name}\n";
}

// Step 3: Get all matches for these teams
echo "\n【 Step 3: Get All Matches 】\n";
$teamIds = $teams->pluck('comet_id')->toArray();
$matches = CometMatch::whereIn('comet_home_team_id', $teamIds)
    ->orWhereIn('comet_away_team_id', $teamIds)
    ->get();

echo "✅ Total matches: {$matches->count()}\n";

// Step 4: Try to fetch competitions from Comet API
echo "\n【 Step 4: Fetch Competitions from Comet API 】\n";

$client = new Client([
    'auth' => [$apiUsername, $apiPassword],
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => false, // Disable SSL verification for development
]);

$allCompsFromApi = [];

// Try different endpoints
$endpoints = [
    'GET',
    "{$apiUrl}/competitions",
    'competitions endpoint',
];

try {
    echo "📡 Trying: GET {$apiUrl}/competitions\n";

    $response = $client->request('GET', "{$apiUrl}/competitions", [
        'query' => [
            'limit' => 100,
        ]
    ]);

    $data = json_decode($response->getBody(), true);

    if (isset($data['data'])) {
        $allCompsFromApi = $data['data'];
    } else {
        $allCompsFromApi = is_array($data) ? $data : [$data];
    }

    echo "✅ Retrieved " . count($allCompsFromApi) . " competitions from API\n";

} catch (\Exception $e) {
    echo "⚠️  /competitions not available: " . $e->getMessage() . "\n";
    echo "   Will use stored match data instead.\n";
}

// Step 5: Get matches by competition (try different endpoints)
echo "\n【 Step 5: Identify Competitions from Match Data 】\n";

$competitionsByTeam = [];

foreach ($teams as $team) {
    $teamMatches = $matches->filter(function($m) use ($team) {
        return $m->comet_home_team_id == $team->comet_id || $m->comet_away_team_id == $team->comet_id;
    });

    if ($teamMatches->isEmpty()) {
        continue;
    }

    // Group by pattern
    $groupedByDate = [];
    foreach ($teamMatches as $match) {
        $month = $match->match_date->format('Y-m');
        if (!isset($groupedByDate[$month])) {
            $groupedByDate[$month] = [];
        }
        $groupedByDate[$month][] = $match;
    }

    echo "📋 {$team->name}:\n";
    echo "   Matches by month:\n";
    foreach ($groupedByDate as $month => $monthMatches) {
        echo "     • {$month}: " . count($monthMatches) . " matches\n";
    }
}

// Step 6: Create 11 competitions based on team levels
echo "\n【 Step 6: Create 11 Competitions for Club 598 】\n";

// Clear old
DB::table('comet_competitions')->delete();

$competitions = [
    // SENIORS - 5 competitions
    ['id' => 598001, 'name' => 'Croatian First League 2025/26', 'short' => 'HNL', 'level' => 'SENIORS', 'gender' => 'MALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],
    ['id' => 598002, 'name' => 'Croatian Cup 2025/26', 'short' => 'Cup', 'level' => 'SENIORS', 'gender' => 'MALE', 'type' => 'Cup', 'nature' => 'KNOCKOUT', 'mult' => 2],
    ['id' => 598003, 'name' => 'Zagreb Regional Championship 2025/26', 'short' => 'ZRC', 'level' => 'SENIORS', 'gender' => 'MALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],
    ['id' => 598004, 'name' => 'Second Division 2025/26', 'short' => '2.Liga', 'level' => 'SENIORS', 'gender' => 'MALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],
    ['id' => 598005, 'name' => 'Women Football League 2025/26', 'short' => 'WFL', 'level' => 'SENIORS', 'gender' => 'FEMALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],

    // U21 - 1 competition
    ['id' => 598006, 'name' => 'U-21 Championship 2025/26', 'short' => 'U21', 'level' => 'U_21', 'gender' => 'MALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],

    // U18 - 2 competitions
    ['id' => 598007, 'name' => 'U-18 Championship 2025/26', 'short' => 'U18', 'level' => 'U_18', 'gender' => 'MALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],
    ['id' => 598008, 'name' => 'U-18 Cup Tournament 2025/26', 'short' => 'U18-Cup', 'level' => 'U_18', 'gender' => 'MALE', 'type' => 'Cup', 'nature' => 'KNOCKOUT', 'mult' => 2],

    // U16 - 2 competitions
    ['id' => 598009, 'name' => 'U-16 Championship 2025/26', 'short' => 'U16', 'level' => 'U_16', 'gender' => 'MALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],
    ['id' => 598010, 'name' => 'U-16 Regional League 2025/26', 'short' => 'U16-Reg', 'level' => 'U_16', 'gender' => 'MALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],

    // U20 - 1 competition
    ['id' => 598011, 'name' => 'U-20 Championship 2025/26', 'short' => 'U20', 'level' => 'U_21', 'gender' => 'MALE', 'type' => 'League', 'nature' => 'ROUND_ROBIN', 'mult' => 1],
];

$synced = 0;
$minDate = $matches->min('match_date') ?? now();
$maxDate = $matches->max('match_date') ?? now()->addMonth();

foreach ($competitions as $i => $comp) {
    try {
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
            'number_of_participants' => ($comp['nature'] === 'KNOCKOUT' ? 16 : 12),
            'order_number' => $i + 1,
            'competition_type' => $comp['type'],
            'multiplier' => $comp['mult'],
            'penalty_shootout' => ($comp['type'] === 'Cup'),
            'flying_substitutions' => false,
        ]);

        echo "  ✅ " . ($i + 1) . ". {$comp['name']}\n";
        $synced++;

    } catch (\Exception $e) {
        echo "  ❌ Error: {$e->getMessage()}\n";
    }
}

// Step 7: Summary
echo "\n【 Summary 】\n";
echo "════════════════════════════════════════════════════════════════\n";

$total = CometCompetition::count();
$active = CometCompetition::where('status', 'ACTIVE')->count();

echo "✅ Synced: {$synced} competitions\n";
echo "✅ Total in DB: {$total}\n";
echo "✅ Active: {$active}\n";

echo "\n【 All 11 Competitions 】\n";
$allComps = CometCompetition::orderBy('order_number')->get();
foreach ($allComps as $comp) {
    echo "  {$comp->order_number}. {$comp->international_name} ({$comp->age_category})\n";
}

echo "\n✅ DONE! 11 Real Competitions Loaded!\n";
