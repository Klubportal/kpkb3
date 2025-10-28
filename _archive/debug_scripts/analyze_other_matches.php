<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "【 Analyzing OTHER Matches 】\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    // Get sample OTHER matches with their competitions
    $otherMatches = DB::table('comet_matches as m')
        ->join('comet_competitions as c', 'm.competition_fifa_id', '=', 'c.comet_id')
        ->where('m.age_category', 'OTHER')
        ->select('m.id', 'm.age_category as match_age', 'c.age_category as comp_age', 'c.name as comp_name')
        ->limit(20)
        ->get();

    echo "Sample of matches with age_category=OTHER:\n\n";
    foreach ($otherMatches as $match) {
        $status = $match->match_age === $match->comp_age ? '✅ SAME' : '❌ DIFFERENT';
        echo "{$status} | Match {$match->id}: match_age={$match->match_age}, comp_age={$match->comp_age}\n";
        echo "        Competition: {$match->comp_name}\n\n";
    }

    // Count how many OTHER matches have OTHER competitions vs non-OTHER competitions
    echo "【 OTHER Matches Analysis 】\n";
    $sameOther = DB::table('comet_matches as m')
        ->join('comet_competitions as c', 'm.competition_fifa_id', '=', 'c.comet_id')
        ->where('m.age_category', 'OTHER')
        ->where('c.age_category', 'OTHER')
        ->count();

    $differentCategory = DB::table('comet_matches as m')
        ->join('comet_competitions as c', 'm.competition_fifa_id', '=', 'c.comet_id')
        ->where('m.age_category', 'OTHER')
        ->where('c.age_category', '!=', 'OTHER')
        ->count();

    echo "Matches with age_category=OTHER where competition is also OTHER: {$sameOther}\n";
    echo "Matches with age_category=OTHER but competition is NOT OTHER: {$differentCategory}\n";

    if ($differentCategory > 0) {
        echo "\n⚠️  Need to update {$differentCategory} matches!\n";
    }
});
