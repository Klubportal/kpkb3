<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking for FK constraints on comet_cases...\n";
$fks = DB::connection('mysql')->select("
    SELECT CONSTRAINT_NAME, TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'comet_cases'
    AND TABLE_SCHEMA = 'kpkb3'
");

foreach ($fks as $fk) {
    echo "Dropping FK: {$fk->CONSTRAINT_NAME} from {$fk->TABLE_NAME}\n";
    DB::connection('mysql')->statement("ALTER TABLE {$fk->TABLE_NAME} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
}

echo "\nDropping comet_cases from central database...\n";
DB::connection('mysql')->statement('DROP TABLE IF EXISTS comet_cases');
echo "✅ comet_cases gelöscht aus kpkb3\n";
