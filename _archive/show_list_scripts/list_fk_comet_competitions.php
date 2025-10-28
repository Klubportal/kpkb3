<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$rows = DB::connection('central')->select("SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'comet_competitions' AND REFERENCED_TABLE_SCHEMA = DATABASE()");
if (empty($rows)) {
    echo "No foreign keys reference comet_competitions\n";
} else {
    foreach ($rows as $r) {
        echo "{$r->TABLE_NAME} -> {$r->CONSTRAINT_NAME} (col {$r->COLUMN_NAME})\n";
    }
}
