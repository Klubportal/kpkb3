<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Initialize tenant
tenancy()->initialize('nk-prigorje-test');

// Get all tables
$tables = DB::connection('tenant')->select('SHOW TABLES');

echo "=== COMET TABLES IN TENANT: nk-prigorje-test ===\n\n";

$cometTables = [];
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (str_starts_with($tableName, 'comet_')) {
        $cometTables[] = $tableName;
    }
}

if (empty($cometTables)) {
    echo "❌ KEINE Comet-Tabellen gefunden!\n";
} else {
    echo "✅ " . count($cometTables) . " Comet-Tabellen gefunden:\n\n";
    foreach ($cometTables as $table) {
        echo "  - $table\n";
    }
}

echo "\n";
