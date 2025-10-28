<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Detailed database comparison\n";
echo "============================\n\n";

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

sort($oldTableNames);
sort($newTableNames);

// Find missing tables in kpkb3
$missingInNew = array_diff($oldTableNames, $newTableNames);

// Find additional tables in kpkb3
$additionalInNew = array_diff($newTableNames, $oldTableNames);

if (count($missingInNew) > 0) {
    echo "❌ MISSING in kpkb3 (" . count($missingInNew) . " tables):\n";
    foreach ($missingInNew as $table) {
        echo "  - {$table}\n";
    }
    echo "\n";
} else {
    echo "✅ No missing tables in kpkb3\n\n";
}

if (count($additionalInNew) > 0) {
    echo "➕ ADDITIONAL in kpkb3 (" . count($additionalInNew) . " tables):\n";
    foreach ($additionalInNew as $table) {
        echo "  - {$table}\n";
    }
    echo "\n";
}

echo "\nTotal:\n";
echo "  klubportal_landlord: " . count($oldTableNames) . " tables\n";
echo "  kpkb3: " . count($newTableNames) . " tables\n";
