<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          COMET RANKINGS TABLE STRUCTURE                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$columns = DB::connection('central')->select('SHOW COLUMNS FROM comet_rankings');

printf("%-30s %-25s %-10s %-10s\n", "FIELD", "TYPE", "NULL", "KEY");
echo str_repeat('-', 80) . "\n";

foreach ($columns as $col) {
    printf(
        "%-30s %-25s %-10s %-10s\n",
        $col->Field,
        $col->Type,
        $col->Null,
        $col->Key
    );
}

echo "\n";

// Zähle Rankings
$count = DB::connection('central')->table('comet_rankings')->count();
echo "Total Rankings: $count\n";
