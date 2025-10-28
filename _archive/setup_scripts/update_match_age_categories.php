<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "【 Updating Match Age Categories from Competitions 】\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    // First check what column name is used
    echo "Checking match-competition relationship...\n";
    $sampleMatch = DB::table('comet_matches')->first();
    $hasCompetitionFifaId = property_exists($sampleMatch, 'competition_fifa_id');
    echo "Uses competition_fifa_id: " . ($hasCompetitionFifaId ? 'YES' : 'NO') . "\n\n";

    // Update matches with age_category from their competition
    $updated = DB::statement("
        UPDATE comet_matches m
        INNER JOIN comet_competitions c ON m.competition_fifa_id = c.comet_id
        SET m.age_category = c.age_category
        WHERE m.competition_fifa_id IS NOT NULL
    ");

    echo "✅ Matches updated\n\n";    // Show distribution
    echo "【 Match Age Category Distribution 】\n";
    $distribution = DB::table('comet_matches')
        ->select('age_category', DB::raw('COUNT(*) as count'))
        ->groupBy('age_category')
        ->orderByDesc('count')
        ->get();

    foreach ($distribution as $d) {
        echo sprintf("%-20s: %4d matches\n", $d->age_category ?? 'NULL', $d->count);
    }
});
