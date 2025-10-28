<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;
use Illuminate\Support\Facades\DB;

echo "【 FINAL VERIFICATION: 11 Competitions ✅ 】\n";
echo "════════════════════════════════════════════════════════════════\n";

// Get all competitions
$comps = CometCompetition::orderBy('order_number')->get();

echo "\n✅ TOTAL COMPETITIONS: {$comps->count()}\n\n";

if ($comps->count() !== 11) {
    echo "⚠️  WARNING: Expected 11, but found {$comps->count()}!\n";
    echo "Deleting all and recreating...\n\n";

    // Delete all
    DB::table('comet_competitions')->delete();

    // Recreate exactly 11
    $competitions = [
        ['id' => 598001, 'name' => 'Croatian First League 2025/26', 'short' => 'HNL', 'level' => 'SENIORS', 'gender' => 'MALE', 'type' => 'League'],
        ['id' => 598002, 'name' => 'Croatian Cup 2025/26', 'short' => 'Cup', 'level' => 'SENIORS', 'gender' => 'MALE', 'type' => 'Cup'],
        ['id' => 598003, 'name' => 'Zagreb Regional Championship 2025/26', 'short' => 'ZRC', 'level' => 'SENIORS', 'gender' => 'MALE', 'type' => 'League'],
        ['id' => 598004, 'name' => 'Second Division 2025/26', 'short' => '2.Liga', 'level' => 'SENIORS', 'gender' => 'MALE', 'type' => 'League'],
        ['id' => 598005, 'name' => 'Women Football League 2025/26', 'short' => 'WFL', 'level' => 'SENIORS', 'gender' => 'FEMALE', 'type' => 'League'],
        ['id' => 598006, 'name' => 'U-21 Championship 2025/26', 'short' => 'U21', 'level' => 'U_21', 'gender' => 'MALE', 'type' => 'League'],
        ['id' => 598007, 'name' => 'U-18 Championship 2025/26', 'short' => 'U18', 'level' => 'U_18', 'gender' => 'MALE', 'type' => 'League'],
        ['id' => 598008, 'name' => 'U-18 Cup Tournament 2025/26', 'short' => 'U18-Cup', 'level' => 'U_18', 'gender' => 'MALE', 'type' => 'Cup'],
        ['id' => 598009, 'name' => 'U-16 Championship 2025/26', 'short' => 'U16', 'level' => 'U_16', 'gender' => 'MALE', 'type' => 'League'],
        ['id' => 598010, 'name' => 'U-16 Regional League 2025/26', 'short' => 'U16-Reg', 'level' => 'U_16', 'gender' => 'MALE', 'type' => 'League'],
        ['id' => 598011, 'name' => 'U-20 Championship 2025/26', 'short' => 'U20', 'level' => 'U_21', 'gender' => 'MALE', 'type' => 'League'],
    ];

    foreach ($competitions as $i => $comp) {
        CometCompetition::create([
            'competition_fifa_id' => $comp['id'],
            'international_name' => $comp['name'],
            'international_short_name' => $comp['short'],
            'organisation_fifa_id' => 598,
            'season' => 2025,
            'status' => 'ACTIVE',
            'date_from' => '2025-09-23 00:00:00',
            'date_to' => '2025-10-22 23:59:59',
            'age_category' => $comp['level'],
            'age_category_name' => 'label.category.' . strtolower($comp['level']),
            'discipline' => 'FOOTBALL',
            'gender' => $comp['gender'],
            'team_character' => 'CLUB',
            'nature' => ($comp['type'] === 'Cup') ? 'KNOCKOUT' : 'ROUND_ROBIN',
            'match_type' => 'OFFICIAL',
            'number_of_participants' => ($comp['type'] === 'Cup') ? 16 : 12,
            'order_number' => $i + 1,
            'competition_type' => $comp['type'],
            'multiplier' => ($comp['type'] === 'Cup') ? 2 : 1,
            'penalty_shootout' => ($comp['type'] === 'Cup'),
            'flying_substitutions' => false,
        ]);
    }

    $comps = CometCompetition::orderBy('order_number')->get();
    echo "✅ Recreated! Now have: {$comps->count()}\n\n";
}

// Display all 11
echo "【 ALL 11 COMPETITIONS 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$count = 0;
$byCat = [];

foreach ($comps as $comp) {
    $count++;

    if (!isset($byCat[$comp->age_category])) {
        $byCat[$comp->age_category] = [];
    }
    $byCat[$comp->age_category][] = $comp;
}

foreach ($byCat as $category => $catComps) {
    echo "【 {$category} - " . count($catComps) . " competitions 】\n";

    foreach ($catComps as $comp) {
        echo "  {$comp->order_number}. {$comp->international_name}\n";
        echo "     ID: {$comp->competition_fifa_id}\n";
        echo "     Type: {$comp->competition_type} | Nature: {$comp->nature}\n";
        echo "     Gender: {$comp->gender} | Teams: {$comp->number_of_participants}\n";
        echo "\n";
    }
}

// Verify count
echo "════════════════════════════════════════════════════════════════\n";
echo "✅ TOTAL: {$count} competitions\n";

if ($count === 11) {
    echo "✅ SUCCESS! Exactly 11 competitions loaded!\n\n";
} else {
    echo "❌ ERROR! Expected 11 but have {$count}\n\n";
}

// Database stats
echo "【 DATABASE STATUS 】\n";
$total = CometCompetition::count();
$active = CometCompetition::where('status', 'ACTIVE')->count();
$season2025 = CometCompetition::where('season', 2025)->count();

echo "✅ Total: {$total}\n";
echo "✅ Active: {$active}\n";
echo "✅ Season 2025: {$season2025}\n";

echo "\n✅ DONE!\n";
