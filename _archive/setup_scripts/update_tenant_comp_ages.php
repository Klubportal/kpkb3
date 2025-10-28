<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

/**
 * Detect age category from competition name (Croatian patterns)
 */
function detectAgeCategoryFromName($name) {
    $name = mb_strtolower($name);

    if (preg_match('/\b(senior|seniori|veterani|veteran|prvenstvo veterana)\b/i', $name)) {
        return 'SENIORS';
    }
    if (preg_match('/\b(junior|juniori|u-?19|u19)\b/i', $name)) {
        return 'JUNIORS';
    }
    if (preg_match('/\b(kadet|kadeti|kadetkinje|u-?17|u17)\b/i', $name)) {
        return 'CADETS';
    }
    if (preg_match('/\b(pionir|pioniri|pionirke|stariji pioniri|u-?15|u15)\b/i', $name)) {
        return 'PIONEERS';
    }
    if (preg_match('/\b(mlađi pioniri|mladi pioniri|limač|limači|limaći|u-?13|u13)\b/i', $name)) {
        return 'YOUNG_PIONEERS';
    }
    if (preg_match('/\b(prstić|prstići|zagić|zagići|početnici|u-?11|u11)\b/i', $name)) {
        return 'BEGINNERS';
    }
    if (preg_match('/\b(žen|women|female|kadetkinje|pionirke)\b/i', $name)) {
        return 'WOMEN';
    }

    return 'OTHER';
}echo "【 Update Tenant Competitions with Name Detection 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    // Get all OTHER competitions
    $otherComps = DB::table('comet_competitions')
        ->where('age_category', 'OTHER')
        ->get();

    echo "Found {$otherComps->count()} competitions with age_category=OTHER\n\n";

    $updated = 0;
    foreach ($otherComps as $comp) {
        $detectedCategory = detectAgeCategoryFromName($comp->name);

        if ($detectedCategory !== 'OTHER') {
            DB::table('comet_competitions')
                ->where('id', $comp->id)
                ->update(['age_category' => $detectedCategory]);

            echo "✅ {$comp->name} → {$detectedCategory}\n";
            $updated++;
        }
    }

    echo "\n【 Update Summary 】\n";
    echo "Detected and updated: {$updated}\n";
    echo "Still OTHER: " . ($otherComps->count() - $updated) . "\n";

    // Show new distribution
    echo "\n【 Final Age Category Distribution 】\n";
    $distribution = DB::table('comet_competitions')
        ->select('age_category', DB::raw('COUNT(*) as count'))
        ->groupBy('age_category')
        ->orderByDesc('count')
        ->get();

    foreach ($distribution as $d) {
        echo sprintf("%-20s: %3d competitions\n", $d->age_category, $d->count);
    }
});

echo "\n✅ TENANT COMPETITIONS UPDATED!\n";
