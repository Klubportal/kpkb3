<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "COMET_PLAYERS table structure:\n";
echo str_repeat("=", 70) . "\n\n";

$columns = Schema::connection('central')->getColumnListing('comet_players');
echo "Columns: " . implode(', ', $columns) . "\n\n";

$sample = DB::connection('central')->table('comet_players')->first();
if ($sample) {
    echo "Sample record:\n";
    foreach ($sample as $key => $value) {
        echo "  $key: $value\n";
    }
}
