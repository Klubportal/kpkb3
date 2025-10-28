<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Comparing databases: klubportal_landlord vs kpkb3\n";
echo "==================================================\n\n";

// Get tables from old database
$oldTables = DB::connection('mysql')->select("SHOW TABLES FROM klubportal_landlord");
$oldTableNames = array_map(function($table) {
    return array_values((array)$table)[0];
}, $oldTables);

// Get tables from new database
$newTables = DB::connection('mysql')->select("SHOW TABLES FROM kpkb3");
$newTableNames = array_map(function($table) {
    return array_values((array)$table)[0];
}, $newTables);

// Find missing tables
$missingTables = array_diff($oldTableNames, $newTableNames);

if (count($missingTables) > 0) {
    echo "❌ MISSING TABLES in kpkb3 (" . count($missingTables) . " tables):\n";
    echo "========================================\n\n";
    foreach ($missingTables as $table) {
        echo "- {$table}\n";
    }

    echo "\n\nDo you want to copy these missing tables? (y/n): ";
} else {
    echo "✅ All tables from klubportal_landlord exist in kpkb3\n";
}

echo "\n\nDatabase Statistics:\n";
echo "====================\n";
echo "klubportal_landlord: " . count($oldTableNames) . " tables\n";
echo "kpkb3: " . count($newTableNames) . " tables\n";
