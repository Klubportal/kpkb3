<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ALLE TABELLEN IN kpkb3 (Central) ===\n\n";

$tables = DB::connection('central')->select('SHOW TABLES');
$tableNames = [];

foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    $tableNames[] = $tableName;
}

sort($tableNames);

echo "Gesamt: " . count($tableNames) . " Tabellen\n\n";

foreach ($tableNames as $name) {
    echo "- {$name}\n";
}

echo "\n=== TENANT TABELLEN ===\n\n";

$tenant = App\Models\Central\Tenant::find('nknapijed');
if ($tenant) {
    tenancy()->initialize($tenant);

    echo "Tenant: {$tenant->name} (Database: tenant_nknapijed)\n\n";

    $tenantTables = DB::connection('tenant')->select('SHOW TABLES');
    $tenantTableNames = [];

    foreach ($tenantTables as $table) {
        $tableName = array_values((array)$table)[0];
        $tenantTableNames[] = $tableName;
    }

    sort($tenantTableNames);

    echo "Gesamt: " . count($tenantTableNames) . " Tabellen\n\n";

    foreach ($tenantTableNames as $name) {
        echo "- {$name}\n";
    }
}
