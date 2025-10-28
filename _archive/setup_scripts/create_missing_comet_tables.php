<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenants = \App\Models\Tenant::all();

echo "=== ERSTELLE FEHLENDE COMET-TABELLEN FÜR ALLE TENANTS ===\n\n";

// Get migration files
$migrationFiles = glob(__DIR__ . '/database/migrations/tenant/2025_10_26_*_create_comet_*.php');

foreach ($tenants as $tenant) {
    echo "Tenant: {$tenant->id}\n";
    echo str_repeat('-', 50) . "\n";

    try {
        tenancy()->initialize($tenant);

        $connection = DB::connection('tenant');

        // Get existing tables
        $tables = $connection->select('SHOW TABLES');
        $existingTables = [];
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $existingTables[] = $tableName;
        }

        // Run each migration file manually
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');

            // Extract table name from migration name
            preg_match('/create_comet_(.+)_table/', $migrationName, $matches);
            if (!isset($matches[1])) continue;

            $tableName = 'comet_' . $matches[1];

            // Skip if table already exists
            if (in_array($tableName, $existingTables)) {
                echo "  ⏭  $tableName (existiert bereits)\n";
                continue;
            }

            // Include and run migration
            $migration = include $file;

            try {
                $migration->up();
                echo "  ✅ $tableName erstellt\n";

                // Record in migrations table
                $connection->table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => 1,
                ]);

            } catch (\Exception $e) {
                echo "  ❌ $tableName - Fehler: " . $e->getMessage() . "\n";
            }
        }

    } catch (\Exception $e) {
        echo "❌ Tenant-Fehler: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo "Fertig!\n";
