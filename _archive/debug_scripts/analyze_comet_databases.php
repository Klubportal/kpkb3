<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔍 Datenbank-Analyse\n";
echo str_repeat("═", 80) . "\n\n";

// Alle Datenbanken anzeigen
$databases = DB::select('SHOW DATABASES');
echo "📊 Verfügbare Datenbanken:\n";
echo str_repeat("─", 80) . "\n";
foreach ($databases as $db) {
    $dbName = $db->Database;
    echo "  - {$dbName}\n";
}

echo "\n" . str_repeat("─", 80) . "\n";

// COMET Tabellen in jeder relevanten DB suchen
$checkDatabases = ['kp_server', 'klubportal', 'kpkb3'];

foreach ($checkDatabases as $dbName) {
    echo "\n📁 Database: {$dbName}\n";
    echo str_repeat("─", 80) . "\n";

    try {
        $tables = DB::select("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE 'comet_%'", [$dbName]);
        $count = $tables[0]->count;

        if ($count > 0) {
            echo "  ✅ {$count} COMET Tabellen gefunden\n";

            // Liste alle COMET Tabellen
            $cometTables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE 'comet_%' ORDER BY TABLE_NAME", [$dbName]);
            foreach ($cometTables as $table) {
                echo "     - {$table->TABLE_NAME}\n";
            }
        } else {
            echo "  ⚠️  Keine COMET Tabellen\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("═", 80) . "\n";
echo "💡 Config-Check:\n";
echo str_repeat("─", 80) . "\n";
echo "  Default Connection: " . config('database.default') . "\n";
echo "  Central DB Name:    " . config('database.connections.central.database') . "\n";
echo str_repeat("═", 80) . "\n\n";
