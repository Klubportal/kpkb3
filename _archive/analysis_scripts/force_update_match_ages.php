<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "【 Force Update Match Age Categories 】\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    // Update ALL matches at once
    $result = DB::update("
        UPDATE comet_matches m
        INNER JOIN comet_competitions c ON m.competition_fifa_id = c.comet_id
        SET m.age_category = c.age_category
    ");

    echo "✅ Updated {$result} matches\n\n";

    // Show distribution
    echo "【 Match Age Category Distribution AFTER Update 】\n";
    $distribution = DB::table('comet_matches')
        ->select('age_category', DB::raw('COUNT(*) as count'))
        ->groupBy('age_category')
        ->orderByDesc('count')
        ->get();

    foreach ($distribution as $d) {
        echo sprintf("%-20s: %4d matches\n", $d->age_category ?? 'NULL', $d->count);
    }

    // Show sample matches with their competition
    echo "\n【 Sample Matches with Age Categories 】\n";
    $samples = DB::table('comet_matches as m')
        ->join('comet_competitions as c', 'm.competition_fifa_id', '=', 'c.comet_id')
        ->select('m.id', 'c.name as competition_name', 'm.age_category as match_age', 'c.age_category as comp_age')
        ->limit(10)
        ->get();

    foreach ($samples as $s) {
        echo "Match {$s->id}: {$s->match_age} (from comp: {$s->comp_age}) - {$s->competition_name}\n";
    }
});
