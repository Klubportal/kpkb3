<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Checking Tenant Database Structure...\n";
echo "============================================================\n\n";

// Get first tenant
$tenants = DB::connection('landlord')->table('tenants')->limit(1)->get();

if ($tenants->isEmpty()) {
    echo "❌ No tenants found\n";
    exit;
}

$tenant = $tenants->first();
echo "📊 Tenant: {$tenant->id}\n";
echo "📊 Database: {$tenant->id}\n\n";

// Connect to tenant database
$tenantDb = $tenant->id;

echo "📋 Tables containing 'coach' or 'group':\n";
echo "------------------------------------------------------------\n";

$tables = DB::connection('mysql')->select("
    SELECT TABLE_NAME
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = ?
    AND (TABLE_NAME LIKE '%coach%' OR TABLE_NAME LIKE '%group%')
    ORDER BY TABLE_NAME
", [$tenantDb]);

if (empty($tables)) {
    echo "❌ No coach or group tables found\n\n";
} else {
    foreach ($tables as $table) {
        $tableName = $table->TABLE_NAME;
        echo "✅ {$tableName}\n";

        // Show columns
        $columns = DB::connection('mysql')->select("
            SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$tenantDb, $tableName]);

        foreach ($columns as $col) {
            echo "   - {$col->COLUMN_NAME} ({$col->COLUMN_TYPE})";
            if ($col->COLUMN_KEY) echo " [{$col->COLUMN_KEY}]";
            echo "\n";
        }
        echo "\n";
    }
}

echo "============================================================\n";
