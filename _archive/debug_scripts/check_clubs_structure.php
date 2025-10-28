<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== comet_clubs_extended STRUKTUR ===\n\n";
$columns = DB::select('DESCRIBE comet_clubs_extended');

foreach ($columns as $col) {
    echo sprintf("%-30s %-20s %s\n", $col->Field, $col->Type, $col->Null);
}
