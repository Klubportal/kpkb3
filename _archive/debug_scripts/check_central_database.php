<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CENTRAL DATABASE (klubporta_landlord) STATUS ===\n\n";

// Get database name
$dbName = DB::connection('central')->getDatabaseName();
echo "Datenbank: $dbName\n\n";

// Get all tables
$tables = DB::connection('central')->select('SHOW TABLES');

echo "=== ALLE TABELLEN ===\n";
$allTables = [];
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    $allTables[] = $tableName;
}

sort($allTables);
foreach ($allTables as $table) {
    echo "  - $table\n";
}

echo "\nGesamt: " . count($allTables) . " Tabellen\n\n";

// Comet tables
echo "=== COMET TABELLEN ===\n";
$cometTables = array_filter($allTables, fn($t) => str_starts_with($t, 'comet_'));
if (empty($cometTables)) {
    echo "❌ Keine Comet-Tabellen\n";
} else {
    echo "✅ " . count($cometTables) . " Comet-Tabellen:\n";
    foreach ($cometTables as $table) {
        // Get row count
        $count = DB::connection('central')->table($table)->count();
        echo "  - $table ($count Einträge)\n";
    }
}

echo "\n=== TENANT TABELLEN ===\n";
$tenantTables = array_filter($allTables, fn($t) => str_starts_with($t, 'tenant'));
echo count($tenantTables) . " Tenant-Tabellen:\n";
foreach ($tenantTables as $table) {
    $count = DB::connection('central')->table($table)->count();
    echo "  - $table ($count Einträge)\n";
}

echo "\n=== ANDERE WICHTIGE TABELLEN ===\n";
$importantTables = ['users', 'plans', 'settings', 'news', 'pages', 'media'];
foreach ($importantTables as $table) {
    if (in_array($table, $allTables)) {
        $count = DB::connection('central')->table($table)->count();
        echo "  ✅ $table ($count Einträge)\n";
    } else {
        echo "  ❌ $table (nicht vorhanden)\n";
    }
}
