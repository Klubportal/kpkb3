<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== COMET_MATCHES Columns (Central DB) ===\n";
$columns = DB::connection('central')->select('SHOW COLUMNS FROM comet_matches');
foreach ($columns as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
}

echo "\n=== Sample Match (Central DB) ===\n";
$sample = DB::connection('central')->table('comet_matches')->first();
if ($sample) {
    foreach ($sample as $key => $value) {
        echo "$key: $value\n";
    }
} else {
    echo "No matches found\n";
}

echo "\n=== Matches for club 396 ===\n";
$count = DB::connection('central')
    ->table('comet_matches')
    ->where(function($query) {
        $query->where('team_fifa_id_home', 396)
              ->orWhere('team_fifa_id_away', 396);
    })
    ->count();
echo "Count: $count\n";
