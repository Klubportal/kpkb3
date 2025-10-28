<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "【 CENTRAL vs TENANT Competition Comparison 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Check central
echo "CENTRAL Database:\n";
$centralComps = DB::connection('central')->table('comet_competitions')
    ->where('name', 'LIKE', '%JUNIORI%')
    ->orWhere('name', 'LIKE', '%KADETI%')
    ->limit(5)
    ->get(['comet_id', 'name', 'age_category']);

foreach ($centralComps as $comp) {
    echo "  {$comp->comet_id} | {$comp->age_category} | {$comp->name}\n";
}

// Check tenant
$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "\nTENANT Database:\n";
    $tenantComps = DB::table('comet_competitions')
        ->where('name', 'LIKE', '%JUNIORI%')
        ->orWhere('name', 'LIKE', '%KADETI%')
        ->limit(5)
        ->get(['comet_id', 'name', 'age_category']);

    foreach ($tenantComps as $comp) {
        echo "  {$comp->comet_id} | {$comp->age_category} | {$comp->name}\n";
    }
});

echo "\n✅ Comparison complete!\n";
