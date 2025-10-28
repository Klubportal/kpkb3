<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "【 Age Category Distribution 】\n";
echo "════════════════════════════════════════════════════════════════\n";

$results = DB::table('comet_competitions')
    ->selectRaw('age_category, COUNT(*) as count')
    ->groupBy('age_category')
    ->orderBy('count', 'DESC')
    ->get();

foreach ($results as $r) {
    echo sprintf("%-20s: %3d\n", $r->age_category, $r->count);
}

echo "\n【 Sample Competitions with Age Categories 】\n";
echo "════════════════════════════════════════════════════════════════\n";

$samples = DB::table('comet_competitions')
    ->select('name', 'age_category', 'season')
    ->whereIn('age_category', ['SENIORS', 'U_21', 'U_19', 'U_17', 'U_15'])
    ->limit(10)
    ->get();

foreach ($samples as $s) {
    echo "✅ {$s->name} ({$s->age_category})\n";
}

echo "\n✅ DONE!\n";
