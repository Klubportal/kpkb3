<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "【 Full Competition Sync: Central → Tenant 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    // Get all competitions from central
    $centralComps = DB::connection('central')->table('comet_competitions')
        ->get();

    echo "Found {$centralComps->count()} competitions in central database\n\n";

    $updated = 0;
    $notFound = 0;

    foreach ($centralComps as $centralComp) {
        // Update in tenant by comet_id
        $result = DB::table('comet_competitions')
            ->where('comet_id', $centralComp->comet_id)
            ->update([
                'age_category' => $centralComp->age_category,
                'name' => $centralComp->name,
                'season' => $centralComp->season,
                'type' => $centralComp->type,
                'gender' => $centralComp->gender,
                'updated_at' => now(),
            ]);

        if ($result > 0) {
            $updated++;
            if ($updated <= 10) {
                echo "✅ Updated: {$centralComp->name} → {$centralComp->age_category}\n";
            }
        } else {
            $notFound++;
        }
    }

    echo "\n【 Sync Summary 】\n";
    echo "Updated: {$updated}\n";
    echo "Not found in tenant: {$notFound}\n";

    // Show new distribution
    echo "\n【 Tenant Age Category Distribution AFTER Sync 】\n";
    $distribution = DB::table('comet_competitions')
        ->select('age_category', DB::raw('COUNT(*) as count'))
        ->groupBy('age_category')
        ->orderByDesc('count')
        ->get();

    foreach ($distribution as $d) {
        echo sprintf("%-20s: %3d competitions\n", $d->age_category, $d->count);
    }
});

echo "\n✅ FULL SYNC COMPLETE!\n";
