<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "📊 COMET Database - Complete Overview\n";
echo str_repeat("═", 80) . "\n\n";

try {
    $tables = DB::connection('central')->select("SHOW TABLES LIKE 'comet_%'");

    $totalRecords = 0;

    echo "✅ Existierende COMET Tabellen:\n";
    echo str_repeat("─", 80) . "\n";

    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        $count = DB::connection('central')->table($tableName)->count();
        $totalRecords += $count;

        printf("  %-45s %10d records\n", $tableName, $count);
    }

    echo str_repeat("─", 80) . "\n";
    echo "  Total Tables: " . count($tables) . "\n";
    echo "  Total Records: " . number_format($totalRecords) . "\n";
    echo str_repeat("═", 80) . "\n\n";

    // Neue Tabellen highlight
    $newTables = [
        'comet_match_phases',
        'comet_match_players',
        'comet_match_officials',
        'comet_match_team_officials',
        'comet_team_officials',
        'comet_facilities',
        'comet_facility_fields',
        'comet_cases',
        'comet_sanctions',
        'comet_own_goal_scorers'
    ];

    echo "🆕 Neu erstellte Tabellen (26. Oktober 2025):\n";
    echo str_repeat("─", 80) . "\n";

    foreach ($newTables as $newTable) {
        $exists = DB::connection('central')->getSchemaBuilder()->hasTable($newTable);
        if ($exists) {
            $count = DB::connection('central')->table($newTable)->count();
            printf("  ✅ %-45s %10d records\n", $newTable, $count);
        } else {
            printf("  ❌ %-45s NOT FOUND\n", $newTable);
        }
    }

    echo str_repeat("═", 80) . "\n\n";

    // Schema Details for new tables
    echo "📋 Schema Details - Neue Tabellen:\n";
    echo str_repeat("─", 80) . "\n\n";

    foreach ($newTables as $newTable) {
        if (DB::connection('central')->getSchemaBuilder()->hasTable($newTable)) {
            $columns = DB::connection('central')->getSchemaBuilder()->getColumnListing($newTable);
            echo "  📁 {$newTable}:\n";
            echo "     Columns: " . implode(', ', array_slice($columns, 0, 8)) . "...\n";
            echo "     Total: " . count($columns) . " columns\n\n";
        }
    }

    echo str_repeat("═", 80) . "\n";
    echo "✅ Database Setup Complete!\n";
    echo str_repeat("═", 80) . "\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}
