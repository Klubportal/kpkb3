<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
tenancy()->initialize('nkprigorjem');

// Show columns
$columns = DB::select('SHOW COLUMNS FROM comet_matches');
echo "=== COMET_MATCHES COLUMNS ===\n\n";
foreach($columns as $col) {
    echo $col->Field . " - " . $col->Type . "\n";
}

// Get sample match with logos
echo "\n=== SAMPLE MATCH DATA ===\n\n";
$match = DB::table('comet_matches')
    ->where('team_fifa_id_home', 598)
    ->orWhere('team_fifa_id_away', 598)
    ->whereNotNull('team_score_home')
    ->first();

if ($match) {
    foreach($match as $key => $value) {
        if (str_contains($key, 'logo') || str_contains($key, 'team_') || str_contains($key, 'home') || str_contains($key, 'away')) {
            echo "$key = " . ($value ?? 'NULL') . "\n";
        }
    }
}
