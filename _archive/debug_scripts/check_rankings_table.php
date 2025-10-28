<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "comet_rankings table structure:\n";
echo str_repeat('=', 80) . "\n";

$columns = DB::connection('central')->select('DESCRIBE comet_rankings');

foreach ($columns as $col) {
    echo sprintf(
        "%-35s %-20s %-5s %-10s %-20s\n",
        $col->Field,
        $col->Type,
        $col->Null,
        $col->Key,
        $col->Default ?? 'NULL'
    );
}
