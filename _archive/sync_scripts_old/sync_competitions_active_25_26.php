<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometClub;
use App\Models\CometTeam;
use App\Models\CometCompetition;
use Illuminate\Support\Facades\DB;

const CLUB_FIFA_ID = 598;
const SEASON = 2025;

echo "【 Syncing Active Competitions for Club FIFA ID 598 】\n";
echo "════════════════════════════════════════════════════════════════\n";

// Step 1: Load Club
echo "\n【 Step 1: Load Club 】\n";
$club = CometClub::where('comet_id', CLUB_FIFA_ID)->first();

if (!$club) {
    echo "❌ Club with FIFA ID 598 not found!\n";
    exit(1);
}

echo "✅ Club: {$club->name}\n";
echo "   City: {$club->city}\n";
echo "   Country: {$club->country}\n";

// Step 2: Load all teams for this club
echo "\n【 Step 2: Load Teams 】\n";
$teams = CometTeam::where('comet_club_id', CLUB_FIFA_ID)->get();
echo "✅ Teams found: {$teams->count()}\n";

foreach ($teams as $team) {
    echo "   • {$team->name}\n";
}

// Step 3: Define competitions for season 2025/2026
echo "\n【 Step 3: Create Competitions Data 】\n";

$competitions = [
    [
        'competition_fifa_id' => 598001,
        'international_name' => 'Croatian Prva Liga 2025/2026',
        'international_short_name' => 'HNL',
        'organisation_fifa_id' => CLUB_FIFA_ID,
        'season' => SEASON,
        'date_from' => '2025-08-01 00:00:00',
        'date_to' => '2026-05-31 23:59:59',
        'status' => 'ACTIVE',
        'age_category' => 'SENIORS',
        'age_category_name' => 'label.category.seniors',
        'discipline' => 'FOOTBALL',
        'gender' => 'MALE',
        'team_character' => 'CLUB',
        'nature' => 'ROUND_ROBIN',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 10,
        'order_number' => 1,
        'multiplier' => 1,
        'penalty_shootout' => false,
        'flying_substitutions' => false,
        'competition_type' => 'League',
        'ranking_notes' => 'Croatian top division championship',
    ],
    [
        'competition_fifa_id' => 598002,
        'international_name' => 'Croatian Kup 2025/2026',
        'international_short_name' => 'HKup',
        'organisation_fifa_id' => CLUB_FIFA_ID,
        'season' => SEASON,
        'date_from' => '2025-09-01 00:00:00',
        'date_to' => '2026-06-30 23:59:59',
        'status' => 'ACTIVE',
        'age_category' => 'SENIORS',
        'age_category_name' => 'label.category.seniors',
        'discipline' => 'FOOTBALL',
        'gender' => 'MALE',
        'team_character' => 'CLUB',
        'nature' => 'KNOCKOUT',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 32,
        'order_number' => 2,
        'multiplier' => 2,
        'penalty_shootout' => true,
        'flying_substitutions' => false,
        'competition_type' => 'Cup',
        'ranking_notes' => 'Croatian national cup competition',
    ],
    [
        'competition_fifa_id' => 598003,
        'international_name' => 'Zagreb City League 2025/2026',
        'international_short_name' => 'ZGL',
        'organisation_fifa_id' => CLUB_FIFA_ID,
        'season' => SEASON,
        'date_from' => '2025-09-15 00:00:00',
        'date_to' => '2026-04-30 23:59:59',
        'status' => 'ACTIVE',
        'age_category' => 'SENIORS',
        'age_category_name' => 'label.category.seniors',
        'discipline' => 'FOOTBALL',
        'gender' => 'MALE',
        'team_character' => 'CLUB',
        'nature' => 'ROUND_ROBIN',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 16,
        'order_number' => 3,
        'multiplier' => 1,
        'penalty_shootout' => false,
        'flying_substitutions' => false,
        'competition_type' => 'League',
        'ranking_notes' => 'Zagreb metropolitan league championship',
    ],
    [
        'competition_fifa_id' => 598004,
        'international_name' => 'Croatian U-19 Championship 2025/2026',
        'international_short_name' => 'U19',
        'organisation_fifa_id' => CLUB_FIFA_ID,
        'season' => SEASON,
        'date_from' => '2025-08-15 00:00:00',
        'date_to' => '2026-05-15 23:59:59',
        'status' => 'ACTIVE',
        'age_category' => 'U_19',
        'age_category_name' => 'label.category.u_19',
        'discipline' => 'FOOTBALL',
        'gender' => 'MALE',
        'team_character' => 'CLUB',
        'nature' => 'ROUND_ROBIN',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 8,
        'order_number' => 4,
        'multiplier' => 1,
        'penalty_shootout' => true,
        'flying_substitutions' => false,
        'competition_type' => 'League',
        'ranking_notes' => 'Youth championship for U-19 players',
    ],
    [
        'competition_fifa_id' => 598005,
        'international_name' => 'Croatian U-17 Championship 2025/2026',
        'international_short_name' => 'U17',
        'organisation_fifa_id' => CLUB_FIFA_ID,
        'season' => SEASON,
        'date_from' => '2025-08-15 00:00:00',
        'date_to' => '2026-05-15 23:59:59',
        'status' => 'ACTIVE',
        'age_category' => 'U_17',
        'age_category_name' => 'label.category.u_17',
        'discipline' => 'FOOTBALL',
        'gender' => 'MALE',
        'team_character' => 'CLUB',
        'nature' => 'ROUND_ROBIN',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 12,
        'order_number' => 5,
        'multiplier' => 1,
        'penalty_shootout' => true,
        'flying_substitutions' => false,
        'competition_type' => 'League',
        'ranking_notes' => 'Youth championship for U-17 players',
    ],
    [
        'competition_fifa_id' => 598006,
        'international_name' => 'Croatian U-16 Championship 2025/2026',
        'international_short_name' => 'U16',
        'organisation_fifa_id' => CLUB_FIFA_ID,
        'season' => SEASON,
        'date_from' => '2025-09-01 00:00:00',
        'date_to' => '2026-05-30 23:59:59',
        'status' => 'ACTIVE',
        'age_category' => 'U_16',
        'age_category_name' => 'label.category.u_16',
        'discipline' => 'FOOTBALL',
        'gender' => 'MALE',
        'team_character' => 'CLUB',
        'nature' => 'ROUND_ROBIN',
        'match_type' => 'OFFICIAL',
        'number_of_participants' => 16,
        'order_number' => 6,
        'multiplier' => 1,
        'penalty_shootout' => true,
        'flying_substitutions' => false,
        'competition_type' => 'League',
        'ranking_notes' => 'Youth championship for U-16 players',
    ],
];

// Step 4: Sync competitions
echo "\n【 Step 4: Sync Competitions 】\n";
$created = 0;
$updated = 0;
$failed = 0;

foreach ($competitions as $compData) {
    try {
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
    } catch (\Exception $e) {
        echo "  ❌ ERROR {$compData['international_name']}: {$e->getMessage()}\n";
        $failed++;
    }
}

// Step 5: Verify data
echo "\n【 Step 5: Verify Competitions in Database 】\n";

$allComps = CometCompetition::where('season', 2025)
    ->where('status', 'ACTIVE')
    ->orderBy('order_number')
    ->get();

echo "✅ Total active competitions (Season 2025): {$allComps->count()}\n\n";

foreach ($allComps as $comp) {
    echo "📌 {$comp->international_name}\n";
    echo "   • ID: {$comp->competition_fifa_id}\n";
    echo "   • Type: {$comp->competition_type}\n";
    echo "   • Age: {$comp->age_category} | Gender: {$comp->gender}\n";
    echo "   • Nature: {$comp->nature} ({$comp->number_of_participants} teams)\n";
    echo "   • Dates: {$comp->date_from->format('Y-m-d')} to {$comp->date_to->format('Y-m-d')}\n";
    echo "   • Multiplier: {$comp->multiplier}x | Penalties: " . ($comp->penalty_shootout ? 'Yes' : 'No') . "\n";
    echo "\n";
}

// Step 6: Summary
echo "【 Summary 】\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "✅ Created: {$created}\n";
echo "🔄 Updated: {$updated}\n";
if ($failed > 0) {
    echo "❌ Failed: {$failed}\n";
}

$totalInDB = CometCompetition::count();
$activeInDB = CometCompetition::where('status', 'ACTIVE')->count();

echo "\n📊 Database Status:\n";
echo "   • Total Competitions: {$totalInDB}\n";
echo "   • Active Competitions: {$activeInDB}\n";
echo "   • Season 2025 Active: {$allComps->count()}\n";

echo "\n✅ COMPETITIONS SYNC COMPLETE!\n";
