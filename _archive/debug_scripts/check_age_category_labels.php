<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "【 Age Category Labels in Competitions 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$labels = DB::table('comet_competitions')
    ->select('age_category', DB::raw('COUNT(*) as count'))
    ->groupBy('age_category')
    ->orderBy('count', 'DESC')
    ->get();

foreach ($labels as $label) {
    echo sprintf("%-20s | %3d competitions\n",
        $label->age_category,
        $label->count
    );
}echo "\n✅ Total: " . $labels->count() . " distinct combinations\n";
