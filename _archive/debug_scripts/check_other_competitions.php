<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "【 Sample OTHER Age Category Competitions 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$comps = DB::table('comet_competitions')
    ->where('age_category', 'OTHER')
    ->limit(30)
    ->get(['name']);

foreach ($comps as $comp) {
    echo "• {$comp->name}\n";
}
