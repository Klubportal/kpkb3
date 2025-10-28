<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
$domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', 'nknapijed.localhost')->first();
if ($domain) {
    tenancy()->initialize($domain->tenant);
}

echo "Structure of comet_player_competition_stats:\n";
echo "============================================\n\n";

$columns = DB::select('DESCRIBE comet_player_competition_stats');
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

echo "\n\nSample data:\n";
echo "============\n\n";

$sample = DB::table('comet_player_competition_stats')
    ->where('club_id', 598)
    ->orderBy('goals', 'desc')
    ->limit(5)
    ->get();

foreach ($sample as $row) {
    print_r($row);
}
