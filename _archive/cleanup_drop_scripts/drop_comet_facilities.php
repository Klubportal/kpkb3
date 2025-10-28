<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Dropping comet_facility_fields and comet_facilities from kpkb3 ===\n\n";

// Drop comet_facility_fields first (child table)
echo "Dropping comet_facility_fields...\n";
$fks = DB::connection('mysql')->select("
    SELECT CONSTRAINT_NAME, TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'comet_facility_fields'
    AND TABLE_SCHEMA = 'kpkb3'
");

foreach ($fks as $fk) {
    echo "  Dropping FK: {$fk->CONSTRAINT_NAME} from {$fk->TABLE_NAME}\n";
    DB::connection('mysql')->statement("ALTER TABLE {$fk->TABLE_NAME} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
}

DB::connection('mysql')->statement('DROP TABLE IF EXISTS comet_facility_fields');
echo "  ✓ Dropped comet_facility_fields\n\n";

// Drop comet_facilities (parent table)
echo "Dropping comet_facilities...\n";
$fks = DB::connection('mysql')->select("
    SELECT CONSTRAINT_NAME, TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'comet_facilities'
    AND TABLE_SCHEMA = 'kpkb3'
");

foreach ($fks as $fk) {
    echo "  Dropping FK: {$fk->CONSTRAINT_NAME} from {$fk->TABLE_NAME}\n";
    DB::connection('mysql')->statement("ALTER TABLE {$fk->TABLE_NAME} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
}

DB::connection('mysql')->statement('DROP TABLE IF EXISTS comet_facilities');
echo "  ✓ Dropped comet_facilities\n\n";

echo "✅ Done!\n";
