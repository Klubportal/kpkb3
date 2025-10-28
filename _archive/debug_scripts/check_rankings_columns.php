<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
tenancy()->initialize('nkprigorjem');

// Show columns
$columns = DB::select('SHOW COLUMNS FROM comet_rankings');
echo "=== COMET_RANKINGS COLUMNS ===\n\n";
foreach($columns as $col) {
    echo $col->Field . " - " . $col->Type . "\n";
}

// Get sample ranking
echo "\n=== SAMPLE RANKING DATA ===\n\n";
$ranking = DB::table('comet_rankings')->first();

if ($ranking) {
    foreach($ranking as $key => $value) {
        echo "$key = " . ($value ?? 'NULL') . "\n";
    }
}
