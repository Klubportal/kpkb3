<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "【 Competition Age Categories in Tenant 】\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    $distribution = DB::table('comet_competitions')
        ->select('age_category', DB::raw('COUNT(*) as count'))
        ->groupBy('age_category')
        ->orderByDesc('count')
        ->get();

    foreach ($distribution as $d) {
        echo sprintf("%-20s: %3d competitions\n", $d->age_category ?? 'NULL', $d->count);
    }

    echo "\n【 Sample Competitions 】\n";
    $samples = DB::table('comet_competitions')
        ->select('name', 'age_category')
        ->limit(10)
        ->get();

    foreach ($samples as $s) {
        echo "• {$s->name} ({$s->age_category})\n";
    }
});
