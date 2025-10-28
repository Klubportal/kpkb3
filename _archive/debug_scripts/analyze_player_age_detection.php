<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "【 Analyzing Player Age Category Detection 】\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    // Get a sample player with OTHER
    $otherPlayer = DB::table('comet_players')
        ->where('primary_age_category', 'OTHER')
        ->first();

    if ($otherPlayer) {
        echo "Sample Player: {$otherPlayer->first_name} {$otherPlayer->last_name}\n";
        echo "Current age_category: {$otherPlayer->primary_age_category}\n\n";

        // Check their matches
        echo "Matches played:\n";
        $matches = DB::table('comet_match_players as mp')
            ->join('comet_matches as m', 'mp.match_fifa_id', '=', 'm.match_fifa_id')
            ->where('mp.person_fifa_id', $otherPlayer->person_fifa_id)
            ->select('m.age_category', DB::raw('COUNT(*) as count'))
            ->groupBy('m.age_category')
            ->orderByDesc('count')
            ->get();

        foreach ($matches as $match) {
            echo "  {$match->age_category}: {$match->count} matches\n";
        }
    }

    // Count players by their match age_categories
    echo "\n【 What age_categories do OTHER players actually play in? 】\n";
    $otherPlayers = DB::table('comet_players')
        ->where('primary_age_category', 'OTHER')
        ->pluck('person_fifa_id');

    $matchCategories = DB::table('comet_match_players as mp')
        ->join('comet_matches as m', 'mp.match_fifa_id', '=', 'm.match_fifa_id')
        ->whereIn('mp.person_fifa_id', $otherPlayers)
        ->select('m.age_category', DB::raw('COUNT(DISTINCT mp.person_fifa_id) as player_count'))
        ->groupBy('m.age_category')
        ->orderByDesc('player_count')
        ->get();

    foreach ($matchCategories as $cat) {
        echo sprintf("%-20s: %3d players play in this category\n", $cat->age_category, $cat->player_count);
    }
});
