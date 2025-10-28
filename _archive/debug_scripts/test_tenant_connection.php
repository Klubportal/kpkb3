<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

echo "🧪 Testing Tenant Database Connection...\n";
echo "========================================\n";

// Get first tenant
$tenant = Tenant::with('domains')->first();

if (!$tenant) {
    echo "❌ No tenants found!\n";
    exit(1);
}

echo "🏢 Testing Tenant: {$tenant->id} ({$tenant->name})\n";
echo "   Domain: {$tenant->domains->first()->domain}\n";
echo "   DB: tenant_{$tenant->id}\n\n";

try {
    // Initialize tenant
    tenancy()->initialize($tenant);

    echo "✅ Tenant context initialized\n";
    echo "✅ Current database: " . DB::connection()->getDatabaseName() . "\n";

    // Check if tenant tables exist
    $tables = DB::select('SHOW TABLES');
    echo "✅ Tables in tenant database: " . count($tables) . "\n";

    // Check for some key tables
    $keyTables = ['users', 'teams', 'players', 'matches'];
    foreach ($keyTables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "   • {$table}: {$count} records\n";
        } catch (Exception $e) {
            echo "   • {$table}: Table not found\n";
        }
    }

    // Test tenant settings
    try {
        $settings = DB::table('settings')->count();
        echo "   • settings: {$settings} records\n";
    } catch (Exception $e) {
        echo "   • settings: Table not found\n";
    }

    echo "\n✅ Tenant connection test successful!\n";

} catch (Exception $e) {
    echo "❌ Error testing tenant: " . $e->getMessage() . "\n";
} finally {
    // End tenancy
    if (tenancy()->initialized) {
        tenancy()->end();
        echo "✅ Returned to central context\n";
    }
}

echo "\n🎯 Test completed!\n";
