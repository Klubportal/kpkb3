<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "【 Manual Sync: Central → Tenant Competitions 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Get tenant's competition IDs
$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    $tenantCompIds = DB::table('comet_competitions')->pluck('comet_id')->toArray();

    echo "Tenant has " . count($tenantCompIds) . " competitions\n";
    echo "Fetching from central DB...\n\n";

    // Get central competitions
    $centralComps = DB::connection('central')
        ->table('comet_competitions')
        ->whereIn('comet_id', $tenantCompIds)
        ->get();

    echo "Found " . count($centralComps) . " competitions in central DB\n";
    echo "Updating...\n\n";

    $updated = 0;
    foreach ($centralComps as $comp) {
        DB::table('comet_competitions')
            ->where('comet_id', $comp->comet_id)
            ->update([
                'age_category' => $comp->age_category,
                'updated_at' => now(),
            ]);
        $updated++;

        if ($updated % 20 == 0) {
            echo "Updated {$updated}...\n";
        }
    }

    echo "\n✅ Updated {$updated} competitions\n";

    // Show new distribution
    echo "\n【 Age Category Distribution After Update 】\n";
    $distribution = DB::table('comet_competitions')
        ->select('age_category', DB::raw('COUNT(*) as count'))
        ->groupBy('age_category')
        ->orderByDesc('count')
        ->get();

    foreach ($distribution as $d) {
        echo sprintf("%-20s: %3d competitions\n", $d->age_category, $d->count);
    }
});
