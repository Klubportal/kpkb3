<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
$domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', 'nknapijed.localhost')->first();
if (!$domain) {
    echo "❌ Tenant 'nknapijed' not found!\n";
    exit(1);
}

tenancy()->initialize($domain->tenant);

echo "Copying all comet_* tables to tenant database\n";
echo "==============================================\n\n";

// Get all tables from central database that start with comet_
$centralTables = DB::connection('mysql')->select("SHOW TABLES FROM kpkb3 LIKE 'comet_%'");
$cometTables = array_map(function($table) {
    return array_values((array)$table)[0];
}, $centralTables);

echo "Found " . count($cometTables) . " comet_* tables in kpkb3:\n";
foreach ($cometTables as $table) {
    echo "  - {$table}\n";
}

// Get existing tables in tenant database
$tenantTables = DB::select("SHOW TABLES");
$tenantTableNames = array_map(function($table) {
    return array_values((array)$table)[0];
}, $tenantTables);

echo "\n\nProcessing tables:\n";
echo "===================\n\n";

$created = 0;
$existing = 0;
$errors = 0;

foreach ($cometTables as $table) {
    try {
        if (in_array($table, $tenantTableNames)) {
            echo "✓ {$table} - already exists\n";
            $existing++;
        } else {
            echo "→ {$table} - creating... ";

            // Get CREATE TABLE statement from central database
            $createStmt = DB::connection('mysql')->select("SHOW CREATE TABLE kpkb3.{$table}");
            $createSql = $createStmt[0]->{'Create Table'};

            // Execute in tenant database
            DB::statement($createSql);

            // Copy data from central to tenant
            $rowCount = DB::connection('mysql')->table("kpkb3.{$table}")->count();
            if ($rowCount > 0) {
                DB::statement("INSERT INTO {$table} SELECT * FROM kpkb3.{$table}");
                echo "✓ created and copied {$rowCount} rows\n";
            } else {
                echo "✓ created (empty)\n";
            }

            $created++;
        }
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n\nSummary:\n";
echo "========\n";
echo "Total tables: " . count($cometTables) . "\n";
echo "Created: {$created}\n";
echo "Already existed: {$existing}\n";
echo "Errors: {$errors}\n";

if ($created > 0) {
    echo "\n✅ Successfully copied {$created} new comet_* tables to tenant database!\n";
}
