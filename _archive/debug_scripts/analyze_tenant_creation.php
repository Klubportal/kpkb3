<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

echo "🔍 ANALYZING TENANT CREATION PROCESS\n";
echo "====================================\n\n";

// Get the tenant
$tenant = Tenant::with('domains')->find('nknapijed');

if (!$tenant) {
    echo "❌ Tenant 'nknapijed' not found!\n";
    exit(1);
}

echo "1️⃣ TENANT INFORMATION\n";
echo "=====================\n";
echo "ID: {$tenant->id}\n";
echo "Name: {$tenant->name}\n";
echo "Email: {$tenant->email}\n";
echo "Created: {$tenant->created_at}\n";
echo "Domains: " . $tenant->domains->pluck('domain')->join(', ') . "\n";

echo "\n2️⃣ DATABASE CHECK\n";
echo "=================\n";

// Check if database exists
$dbName = "tenant_{$tenant->id}";
try {
    $dbExists = DB::connection('central')->select(
        "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
        [$dbName]
    );

    if (count($dbExists) > 0) {
        echo "✅ Database '{$dbName}' exists\n";

        // Initialize tenant to check tables
        tenancy()->initialize($tenant);

        // Get table count
        $tables = DB::select("SHOW TABLES");
        echo "✅ Tables in database: " . count($tables) . "\n";

        // Check migrations
        $migrations = DB::table('migrations')->count();
        echo "✅ Migrations run: {$migrations}\n";

        // Check if groups table exists
        $groupsExists = DB::select("SHOW TABLES LIKE 'groups'");
        if (count($groupsExists) > 0) {
            echo "✅ 'groups' table exists\n";
        } else {
            echo "❌ 'groups' table is MISSING\n";
        }

        tenancy()->end();

    } else {
        echo "❌ Database '{$dbName}' does NOT exist!\n";
        echo "   This means the automatic database creation failed.\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking database: " . $e->getMessage() . "\n";
}

echo "\n3️⃣ CHECKING TENANCY PIPELINE CONFIGURATION\n";
echo "===========================================\n";

// Read TenancyServiceProvider
$providerPath = app_path('Providers/TenancyServiceProvider.php');
if (file_exists($providerPath)) {
    $content = file_get_contents($providerPath);

    // Check for CreateDatabase job
    if (strpos($content, 'Jobs\CreateDatabase::class') !== false) {
        echo "✅ CreateDatabase job is in pipeline\n";
    } else {
        echo "❌ CreateDatabase job is MISSING from pipeline\n";
    }

    // Check for MigrateDatabase job
    if (strpos($content, 'Jobs\MigrateDatabase::class') !== false) {
        echo "✅ MigrateDatabase job is in pipeline\n";
    } else {
        echo "❌ MigrateDatabase job is MISSING from pipeline\n";
    }

    // Check for SeedDatabase job
    if (strpos($content, 'Jobs\SeedDatabase::class') !== false) {
        if (strpos($content, '// Jobs\SeedDatabase::class') !== false) {
            echo "⚠️  SeedDatabase job is COMMENTED OUT\n";
        } else {
            echo "✅ SeedDatabase job is in pipeline\n";
        }
    } else {
        echo "❌ SeedDatabase job is MISSING from pipeline\n";
    }
} else {
    echo "❌ TenancyServiceProvider.php not found\n";
}

echo "\n4️⃣ CHECKING EVENT LISTENERS\n";
echo "============================\n";

// Check if TenantCreated event has listeners
$eventsPath = app_path('Providers/EventServiceProvider.php');
if (file_exists($eventsPath)) {
    echo "✅ EventServiceProvider exists\n";
} else {
    echo "⚠️  EventServiceProvider not found (might be using attributes)\n";
}

echo "\n5️⃣ SOLUTION\n";
echo "===========\n";

if (count($dbExists ?? []) == 0) {
    echo "The database was NOT created automatically. Options:\n\n";
    echo "A) Create database and run migrations manually:\n";
    echo "   1. php artisan tenants:create-database --tenants=nknapijed\n";
    echo "   2. php artisan tenants:migrate --tenants=nknapijed\n\n";
    echo "B) Check TenancyServiceProvider pipeline configuration\n";
    echo "   Make sure these jobs are enabled:\n";
    echo "   - Jobs\CreateDatabase::class\n";
    echo "   - Jobs\MigrateDatabase::class\n";
} else {
    echo "Database exists but is missing the 'groups' table.\n\n";
    echo "This means migrations didn't run completely. Run:\n";
    echo "php artisan tenants:migrate --tenants=nknapijed --fresh\n";
    echo "\nOR manually create the table:\n";
    echo "php artisan tenants:run nknapijed --artisan='migrate --path=database/migrations/2025_10_26_212528_create_groups_table.php'\n";
}

echo "\n💡 TIP: Check logs/laravel.log for errors during tenant creation\n";
