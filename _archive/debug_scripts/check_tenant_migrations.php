<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

echo "🔍 CHECKING TENANT DATABASE MIGRATIONS\n";
echo "======================================\n\n";

// Get the tenant
$tenant = Tenant::find('nknapijed');

if (!$tenant) {
    echo "❌ Tenant 'nknapijed' not found!\n";
    exit(1);
}

echo "Tenant: {$tenant->name} ({$tenant->id})\n";
echo "Database: tenant_{$tenant->id}\n\n";

// Initialize tenant context
tenancy()->initialize($tenant);

echo "📊 CHECKING TABLES IN TENANT DATABASE\n";
echo "=====================================\n";

try {
    $tables = DB::select("SHOW TABLES");
    $tableList = [];
    $dbName = "tenant_{$tenant->id}";

    foreach ($tables as $table) {
        $tableName = $table->{"Tables_in_{$dbName}"};
        $tableList[] = $tableName;
    }

    echo "Total tables: " . count($tableList) . "\n\n";

    // Check for groups table
    if (in_array('groups', $tableList)) {
        echo "✅ 'groups' table exists\n";
    } else {
        echo "❌ 'groups' table is MISSING\n";
    }

    // Check for other important tables
    $importantTables = [
        'users',
        'comet_players',
        'coach_group',
        'migrations'
    ];

    echo "\nChecking other important tables:\n";
    foreach ($importantTables as $tableName) {
        $exists = in_array($tableName, $tableList);
        echo ($exists ? "✅" : "❌") . " {$tableName}\n";
    }

    // Check migrations status
    echo "\n📋 CHECKING MIGRATION STATUS\n";
    echo "============================\n";

    $migrations = DB::table('migrations')->orderBy('batch')->get();
    $latestBatch = $migrations->max('batch');

    echo "Total migrations run: " . $migrations->count() . "\n";
    echo "Latest batch: {$latestBatch}\n\n";

    // Check for groups migration
    $groupsMigration = $migrations->where('migration', '2025_10_26_212528_create_groups_table')->first();

    if ($groupsMigration) {
        echo "✅ Groups migration found in database (batch {$groupsMigration->batch})\n";
    } else {
        echo "❌ Groups migration NOT found in migrations table\n";
        echo "   This means the migration needs to be run for this tenant\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

tenancy()->end();

echo "\n🔧 SOLUTION\n";
echo "===========\n";
echo "Run the following command to migrate this tenant:\n";
echo "php artisan tenants:migrate --tenants=nknapijed\n";
