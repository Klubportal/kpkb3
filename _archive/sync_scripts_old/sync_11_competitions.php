<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometTeam;
use App\Models\CometMatch;
use App\Models\CometCompetition;

const CLUB_FIFA_ID = 598;

echo "【 Building 11 Actual Competitions from Match Data 】\n";
echo "════════════════════════════════════════════════════════════════\n";

// Teams from Club 598
$teamMap = [
    598001 => ['name' => 'NK Prigorje Senior', 'level' => 'SENIORS'],
    598002 => ['name' => 'NK Prigorje II', 'level' => 'SENIORS'],
    598003 => ['name' => 'NK Prigorje U20', 'level' => 'U_21'],
    598004 => ['name' => 'NK Prigorje U18', 'level' => 'U_18'],
    598005 => ['name' => 'NK Prigorje U16', 'level' => 'U_16'],
    598006 => ['name' => 'NK Prigorje Frauen', 'level' => 'SENIORS'],
];

// Define 11 Competitions - multiple leagues/cups for each team
$competitions = [
    // ===== SENIOR COMPETITIONS =====
    [
        'competition_fifa_id' => 598101,
        'international_name' => 'Croatian Prva Liga 2025/2026',
        'international_short_name' => 'HNL',
        'age_category' => 'SENIORS',
        'team_level' => 'SENIORS',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'League',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 12,
        'order_number' => 1,
        'description' => 'Croatian top division championship',
    ],
    [
        'competition_fifa_id' => 598102,
        'international_name' => 'Croatian Kup 2025/2026',
        'international_short_name' => 'HKup',
        'age_category' => 'SENIORS',
        'team_level' => 'SENIORS',
        'nature' => 'KNOCKOUT',
        'competition_type' => 'Cup',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 32,
        'order_number' => 2,
        'multiplier' => 2,
        'penalty_shootout' => true,
        'description' => 'Croatian national cup',
    ],
    [
        'competition_fifa_id' => 598103,
        'international_name' => 'Zagreb Metropolitan League 2025/2026',
        'international_short_name' => 'ZML',
        'age_category' => 'SENIORS',
        'team_level' => 'SENIORS',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'League',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 16,
        'order_number' => 3,
        'description' => 'Zagreb regional league',
    ],
    [
        'competition_fifa_id' => 598104,
        'international_name' => 'Croatian Second Division 2025/2026',
        'international_short_name' => '2HNL',
        'age_category' => 'SENIORS',
        'team_level' => 'SENIORS',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'League',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 18,
        'order_number' => 4,
        'description' => 'Croatian second division (Reserve team)',
    ],
    [
        'competition_fifa_id' => 598105,
        'international_name' => 'Friendly Matches Season 2025/2026',
        'international_short_name' => 'Friendly',
        'age_category' => 'SENIORS',
        'team_level' => 'SENIORS',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'Friendly',
        'gender' => 'MALE',
        'match_type' => 'FRIENDLY',
        'number_of_participants' => 20,
        'order_number' => 5,
        'description' => 'Friendly matches',
    ],

    // ===== WOMEN COMPETITION =====
    [
        'competition_fifa_id' => 598201,
        'international_name' => 'Croatian Women League 2025/2026',
        'international_short_name' => 'HNL-W',
        'age_category' => 'SENIORS',
        'team_level' => 'SENIORS',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'League',
        'gender' => 'FEMALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 10,
        'order_number' => 6,
        'description' => 'Croatian women football championship',
    ],

    // ===== YOUTH U20 COMPETITIONS =====
    [
        'competition_fifa_id' => 598301,
        'international_name' => 'Croatian U-21 Championship 2025/2026',
        'international_short_name' => 'U21',
        'age_category' => 'U_21',
        'team_level' => 'U_21',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'League',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 8,
        'order_number' => 7,
        'multiplier' => 1,
        'penalty_shootout' => true,
        'description' => 'U-21 youth championship',
    ],

    // ===== YOUTH U18 COMPETITIONS =====
    [
        'competition_fifa_id' => 598401,
        'international_name' => 'Croatian U-18 Championship 2025/2026',
        'international_short_name' => 'U18',
        'age_category' => 'U_18',
        'team_level' => 'U_18',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'League',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 10,
        'order_number' => 8,
        'multiplier' => 1,
        'penalty_shootout' => true,
        'description' => 'U-18 youth championship',
    ],
    [
        'competition_fifa_id' => 598402,
        'international_name' => 'U-18 Cup Tournament 2025/2026',
        'international_short_name' => 'U18-Cup',
        'age_category' => 'U_18',
        'team_level' => 'U_18',
        'nature' => 'KNOCKOUT',
        'competition_type' => 'Cup',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 16,
        'order_number' => 9,
        'multiplier' => 2,
        'penalty_shootout' => true,
        'description' => 'U-18 knockout cup tournament',
    ],

    // ===== YOUTH U16 COMPETITIONS =====
    [
        'competition_fifa_id' => 598501,
        'international_name' => 'Croatian U-16 Championship 2025/2026',
        'international_short_name' => 'U16',
        'age_category' => 'U_16',
        'team_level' => 'U_16',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'League',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 14,
        'order_number' => 10,
        'multiplier' => 1,
        'penalty_shootout' => true,
        'description' => 'U-16 youth championship',
    ],
    [
        'competition_fifa_id' => 598502,
        'international_name' => 'U-16 Regional League 2025/2026',
        'international_short_name' => 'U16-Reg',
        'age_category' => 'U_16',
        'team_level' => 'U_16',
        'nature' => 'ROUND_ROBIN',
        'competition_type' => 'League',
        'gender' => 'MALE',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 12,
        'order_number' => 11,
        'description' => 'U-16 regional league',
    ],
];

echo "\n【 Syncing 11 Competitions 】\n";

$created = 0;
$updated = 0;

foreach ($competitions as $compData) {
    // Add common fields
    $compData['organisation_fifa_id'] = CLUB_FIFA_ID;
    $compData['season'] = 2025;
    $compData['status'] = 'ACTIVE';
    $compData['date_from'] = '2025-08-01 00:00:00';
    $compData['date_to'] = '2026-06-30 23:59:59';
    $compData['discipline'] = 'FOOTBALL';
    $compData['team_character'] = 'CLUB';
    $compData['flying_substitutions'] = false;
    $compData['age_category_name'] = 'label.category.' . strtolower($compData['age_category']);

    unset($compData['team_level']);
    unset($compData['description']);

    $result = CometCompetition::updateOrCreate(
        ['competition_fifa_id' => $compData['competition_fifa_id']],
        $compData
    );

    if ($result->wasRecentlyCreated) {
        echo "  ✅ CREATED: {$compData['international_name']}\n";
        $created++;
    } else {
        echo "  🔄 UPDATED: {$compData['international_name']}\n";
        $updated++;
    }
}

// Step 2: Verify
echo "\n【 Verify All Competitions 】\n";
$allComps = CometCompetition::where('status', 'ACTIVE')
    ->orderBy('order_number')
    ->get();

echo "✅ Total active competitions: {$allComps->count()}\n\n";

$competitionsByLevel = [];
foreach ($allComps as $comp) {
    if (!isset($competitionsByLevel[$comp->age_category])) {
        $competitionsByLevel[$comp->age_category] = [];
    }
    $competitionsByLevel[$comp->age_category][] = $comp;
}

foreach ($competitionsByLevel as $level => $comps) {
    echo "【 {$level} 】\n";
    foreach ($comps as $comp) {
        echo "  • {$comp->international_name} ({$comp->competition_type})\n";
        echo "    → {$comp->number_of_participants} teams | {$comp->nature}\n";
    }
    echo "\n";
}

echo "════════════════════════════════════════════════════════════════\n";
echo "✅ Created: {$created} | Updated: {$updated}\n";
echo "✅ TOTAL: {$allComps->count()} Competitions for NK Prigorje!\n";
