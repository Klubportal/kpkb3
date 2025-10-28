<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "🔍 ANALYZING MISSING MIGRATIONS\n";
echo "================================\n\n";

// Get all migration files
$migrationPath = database_path('migrations');
$migrationFiles = File::files($migrationPath);

echo "Total migration files in database/migrations: " . count($migrationFiles) . "\n\n";

// Get what's in a fresh tenant
$testTenant = DB::table('tenants')->where('id', 'testneuerclub1761599176')->first();

if (!$testTenant) {
    echo "❌ Test tenant not found. Looking for any tenant...\n";
    $testTenant = DB::table('tenants')->first();
}

if ($testTenant) {
    echo "Checking tenant: {$testTenant->id}\n";

    // Switch to tenant database
    $tenantDb = 'tenant_' . $testTenant->id;
    config(['database.connections.tenant.database' => $tenantDb]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    // Get migrations that ran
    $ranMigrations = DB::connection('tenant')
        ->table('migrations')
        ->pluck('migration')
        ->toArray();

    echo "Migrations that ran: " . count($ranMigrations) . "\n\n";

    // Find missing migrations
    $missingMigrations = [];

    foreach ($migrationFiles as $file) {
        $migrationName = str_replace('.php', '', $file->getFilename());

        if (!in_array($migrationName, $ranMigrations)) {
            $missingMigrations[] = $migrationName;
        }
    }

    if (count($missingMigrations) > 0) {
        echo "❌ MISSING MIGRATIONS (" . count($missingMigrations) . "):\n";
        echo "============================\n";
        foreach ($missingMigrations as $missing) {
            echo "   - {$missing}\n";
        }
    } else {
        echo "✅ All migrations have run!\n";
    }

    echo "\n\n📋 LAST 10 MIGRATIONS THAT RAN:\n";
    echo "================================\n";
    $lastMigrations = DB::connection('tenant')
        ->table('migrations')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();

    foreach ($lastMigrations as $migration) {
        echo "   Batch {$migration->batch}: {$migration->migration}\n";
    }

} else {
    echo "❌ No tenants found to check\n";
}
