<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🗄️  Checking Databases for Comet API Credentials\n";
echo str_repeat("═", 80) . "\n\n";

// Show available databases
try {
    $databases = DB::select('SHOW DATABASES');

    echo "📋 Available Databases:\n";
    echo str_repeat("─", 80) . "\n";
    foreach ($databases as $db) {
        $dbName = $db->Database ?? $db->database ?? null;
        echo "  - $dbName\n";
    }
    echo "\n";

    // Try to connect to kp_server
    echo str_repeat("═", 80) . "\n";
    echo "🔍 Looking for 'kp_server' database...\n";
    echo str_repeat("─", 80) . "\n\n";

    try {
        $kpServerTables = DB::connection('mysql')->select('
            SELECT TABLE_NAME
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = "kp_server"
        ');

        if (count($kpServerTables) > 0) {
            echo "✅ Found 'kp_server' database with tables:\n\n";
            foreach ($kpServerTables as $table) {
                $tableName = $table->TABLE_NAME ?? $table->table_name ?? null;
                echo "  - $tableName\n";
            }
            echo "\n";

            // Look for config or credentials table
            $credentialTables = ['configs', 'settings', 'credentials', 'api_keys', 'integrations'];
            foreach ($credentialTables as $credTable) {
                foreach ($kpServerTables as $table) {
                    $tableName = $table->TABLE_NAME ?? $table->table_name ?? null;
                    if (strtolower($tableName) === strtolower($credTable)) {
                        echo str_repeat("─", 80) . "\n";
                        echo "📍 Found credentials table: $tableName\n";
                        echo str_repeat("─", 80) . "\n\n";

                        $data = DB::connection('mysql')->select("SELECT * FROM kp_server.$tableName LIMIT 20");
                        if (count($data) > 0) {
                            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
                        }
                    }
                }
            }
        } else {
            echo "❌ 'kp_server' database exists but has no tables\n\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  'kp_server' database not found or not accessible\n";
        echo "Error: " . $e->getMessage() . "\n\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo str_repeat("═", 80) . "\n";
echo "✅ Database search complete\n";
echo str_repeat("═", 80) . "\n\n";
