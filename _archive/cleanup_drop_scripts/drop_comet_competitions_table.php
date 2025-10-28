<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::connection('central')->statement('DROP TABLE IF EXISTS comet_competitions');
    echo "Dropped comet_competitions from central\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
