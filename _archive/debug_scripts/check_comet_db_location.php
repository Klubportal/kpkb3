<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔍 COMET Tabellen - Datenbank Check\n";
echo str_repeat("═", 80) . "\n\n";

// Check Central DB (kp_server)
echo "📊 Central Database (kp_server):\n";
echo str_repeat("─", 80) . "\n";
try {
    $tables = DB::connection('central')->select("SHOW TABLES LIKE 'comet_%'");
    echo "  Anzahl COMET Tabellen: " . count($tables) . "\n";
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            echo "  ✅ {$tableName}\n";
        }
    }
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
echo str_repeat("─", 80) . "\n";

// Check Landlord DB (klubportal)
echo "📊 Landlord Database (klubportal):\n";
echo str_repeat("─", 80) . "\n";
try {
    $tablesLandlord = DB::connection('landlord')->select("SHOW TABLES LIKE 'comet_%'");
    echo "  Anzahl COMET Tabellen: " . count($tablesLandlord) . "\n";
    if (count($tablesLandlord) > 0) {
        foreach ($tablesLandlord as $table) {
            $tableName = array_values((array)$table)[0];
            echo "  ✅ {$tableName}\n";
        }
    } else {
        echo "  ⚠️  Keine COMET Tabellen gefunden!\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "💡 Hinweis:\n";
echo "   - Central (kp_server): Multi-Tenant Admin Database\n";
echo "   - Landlord (klubportal): Tenancy System Database\n";
echo str_repeat("═", 80) . "\n\n";
