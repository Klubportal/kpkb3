<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🧪 TESTING AUTOMATIC TENANT CREATION\n";
echo "====================================\n\n";

$testTenantId = 'testneuerclub' . time();
$testTenantName = 'Test Neuer Club ' . date('H:i:s');
$testEmail = 'test@' . $testTenantId . '.test';

echo "Creating test tenant:\n";
echo "   ID: {$testTenantId}\n";
echo "   Name: {$testTenantName}\n";
echo "   Email: {$testEmail}\n\n";

try {
    // 1. Create tenant (this should trigger TenancyServiceProvider pipeline)
    echo "Step 1: Creating tenant record...\n";
    $tenant = Tenant::create([
        'id' => $testTenantId,
        'name' => $testTenantName,
        'email' => $testEmail,
    ]);
    echo "   ✅ Tenant created\n\n";

    // Give it a moment to complete pipeline jobs
    sleep(2);

    // 2. Check if database was created
    echo "Step 2: Checking database creation...\n";
    $databases = DB::select('SHOW DATABASES');
    $dbExists = false;
    $expectedDb = 'tenant_' . $testTenantId;

    foreach ($databases as $database) {
        $dbName = $database->Database;
        if ($dbName === $expectedDb) {
            $dbExists = true;
            break;
        }
    }

    if ($dbExists) {
        echo "   ✅ Database '{$expectedDb}' created\n\n";
    } else {
        echo "   ❌ Database '{$expectedDb}' NOT found!\n\n";
        throw new Exception("Database was not created");
    }

    // 3. Initialize tenant and check tables
    echo "Step 3: Checking migrations/tables...\n";
    tenancy()->initialize($tenant);

    // Get all tables
    $tables = DB::select('SHOW TABLES');
    $tableCount = count($tables);
    echo "   Total tables: {$tableCount}\n";

    // Check specific required tables
    $requiredTables = [
        'migrations',
        'settings',
        'users',
        'groups',
        'coach_group',
        'club_players',
        'comet_clubs_extended',
        'comet_players',
        'comet_competitions',
        'comet_matches',
        'comet_syncs'
    ];

    $missingTables = [];
    foreach ($requiredTables as $table) {
        if (Schema::hasTable($table)) {
            echo "   ✅ {$table}\n";
        } else {
            echo "   ❌ {$table} MISSING!\n";
            $missingTables[] = $table;
        }
    }

    echo "\n";

    // 4. Check migrations ran
    echo "Step 4: Checking migrations...\n";
    $migrations = DB::table('migrations')->get();
    echo "   Migrations run: {$migrations->count()}\n";

    // Check for specific recent migrations
    $groupsMigration = DB::table('migrations')
        ->where('migration', 'like', '%create_groups_table%')
        ->first();

    if ($groupsMigration) {
        echo "   ✅ Groups migration found\n";
    } else {
        echo "   ❌ Groups migration NOT found!\n";
    }

    echo "\n";

    // 5. Check default data
    echo "Step 5: Checking default data...\n";

    // Check settings
    $settingsCount = DB::table('settings')->count();
    echo "   Settings records: {$settingsCount}\n";

    // Check for admin user
    $adminUser = DB::table('users')->where('email', 'admin@' . $testTenantId . '.test')->first();
    if ($adminUser) {
        echo "   ✅ Admin user created: {$adminUser->email}\n";
    } else {
        echo "   ⚠️ Admin user not found (might be expected)\n";
    }


    // Check for COMET club record
    $cometClub = DB::table('comet_clubs_extended')->first();
    if ($cometClub) {
        echo "   ✅ COMET club record created\n";
    } else {
        echo "   ⚠️ COMET club record not found (expected on fresh tenant)\n";
    }

    tenancy()->end();    echo "\n";

    // 6. Summary
    echo "📊 SUMMARY\n";
    echo "==========\n";

    if (count($missingTables) === 0 && $migrations->count() > 30) {
        echo "✅ Tenant creation SUCCESSFUL!\n";
        echo "   - Database created\n";
        echo "   - All required tables present\n";
        echo "   - {$migrations->count()} migrations executed\n";
        echo "   - Default settings created\n\n";

        echo "🎉 The automatic tenant creation is working correctly!\n";
    } else {
        echo "❌ Tenant creation INCOMPLETE!\n";
        if (count($missingTables) > 0) {
            echo "   Missing tables: " . implode(', ', $missingTables) . "\n";
        }
        if ($migrations->count() <= 30) {
            echo "   Only {$migrations->count()} migrations ran (expected ~40+)\n";
        }
    }

    echo "\n";

    // 7. Cleanup
    echo "Step 6: Cleanup...\n";
    $cleanup = readline("Delete test tenant? (y/n): ");

    if (strtolower($cleanup) === 'y') {
        tenancy()->initialize($tenant);

        // Drop all tables
        $tables = DB::select('SHOW TABLES');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        tenancy()->end();

        // Drop database
        DB::statement("DROP DATABASE IF EXISTS `{$expectedDb}`");

        // Delete tenant record
        $tenant->delete();

        echo "   ✅ Test tenant deleted\n";
    } else {
        echo "   ⚠️ Test tenant kept: {$testTenantId}\n";
        echo "   You can access it at: http://{$testTenantId}.localhost:8000\n";
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";

    // Attempt cleanup
    if (isset($tenant) && $tenant->exists) {
        echo "\nAttempting cleanup...\n";
        try {
            if (isset($expectedDb)) {
                DB::statement("DROP DATABASE IF EXISTS `{$expectedDb}`");
            }
            $tenant->delete();
            echo "✅ Cleanup successful\n";
        } catch (Exception $cleanupError) {
            echo "❌ Cleanup failed: " . $cleanupError->getMessage() . "\n";
        }
    }
}
