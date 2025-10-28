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

    // Get competitions of OTHER matches
    $otherMatchComps = DB::table('comet_matches as m')
        ->join('comet_competitions as c', 'm.competition_fifa_id', '=', 'c.comet_id')
        ->where('m.age_category', 'OTHER')
        ->select('c.name', 'c.age_category', DB::raw('COUNT(*) as match_count'))
        ->groupBy('c.name', 'c.age_category')
        ->orderByDesc('match_count')
        ->limit(20)
        ->get();

    echo "Top 20 competitions with OTHER matches:\n\n";
    foreach ($otherMatchComps as $comp) {
        echo sprintf("%-60s | %-15s | %3d matches\n",
            substr($comp->name, 0, 60),
            $comp->age_category,
            $comp->match_count
        );
    }
});
