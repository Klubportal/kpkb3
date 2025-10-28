<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔍 SEARCHING FOR KP_API REST API CREDENTIALS IN KP_SERVER\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // Look for config/api tables
    echo "Step 1: Check for config tables in kp_server\n";
    echo str_repeat("─", 80) . "\n\n";

    $tables = DB::connection('mysql')
        ->select("SELECT TABLE_NAME FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = 'kp_server'
                 AND TABLE_NAME LIKE '%config%' OR TABLE_NAME LIKE '%api%' OR TABLE_NAME LIKE '%credential%'");

    if (count($tables) > 0) {
        echo "Found config/API tables:\n";
        foreach ($tables as $table) {
            echo "  - " . $table->TABLE_NAME . "\n";
        }
        echo "\n";
    } else {
        echo "No specific config tables found\n\n";
    }

    // Check all tables and search for API-related data
    echo str_repeat("═", 80) . "\n";
    echo "Step 2: Search for 'kp_api' in kp_server database\n";
    echo str_repeat("─", 80) . "\n\n";

    // Look in generals table or settings
    try {
        $generals = DB::connection('mysql')
            ->table('kp_server.generals')
            ->get();

        if (count($generals) > 0) {
            echo "Found in generals table:\n\n";
            foreach ($generals as $general) {
                echo json_encode($general, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
            }
        }
    } catch (\Exception $e) {
        echo "❌ generals table error\n\n";
    }

    // Try club_settings
    try {
        echo str_repeat("─", 80) . "\n";
        echo "Checking club_settings table:\n";
        echo str_repeat("─", 80) . "\n\n";

        $clubSettings = DB::connection('mysql')
            ->table('kp_server.club_settings')
            ->limit(10)
            ->get();

        if (count($clubSettings) > 0) {
            echo "Found " . count($clubSettings) . " settings:\n\n";
            foreach ($clubSettings as $setting) {
                echo json_encode($setting, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
            }
        }
    } catch (\Exception $e) {
        echo "❌ club_settings table error\n\n";
    }

    // Try to find it in environment or config files
    echo str_repeat("═", 80) . "\n";
    echo "Step 3: Check for environment variables / config files\n";
    echo str_repeat("─", 80) . "\n\n";

    $envPath = '/xampp/htdocs/kp_server/.env';
    if (file_exists($envPath)) {
        echo "Found kp_server/.env file\n";
        $env = file_get_contents($envPath);
        $lines = explode("\n", $env);

        echo "Relevant lines:\n";
        foreach ($lines as $line) {
            if (stripos($line, 'api') !== false || stripos($line, 'comet') !== false ||
                stripos($line, 'rest') !== false || stripos($line, 'url') !== false) {
                echo "  " . $line . "\n";
            }
        }
        echo "\n";
    } else {
        echo "❌ kp_server/.env not found at $envPath\n\n";
    }

    // Try to find PHP config files
    echo str_repeat("─", 80) . "\n";
    echo "Step 4: Looking for config files\n";
    echo str_repeat("─", 80) . "\n\n";

    $configPath = 'C:\\xampp\\htdocs\\kp_server\\config';
    if (is_dir($configPath)) {
        echo "Scanning config directory...\n\n";
        $files = scandir($configPath);
        foreach ($files as $file) {
            if (str_ends_with($file, '.php')) {
                echo "  - $file\n";
            }
        }
        echo "\n";

        // Try to read services.php if exists
        if (file_exists($configPath . '\\services.php')) {
            echo "\nReading config/services.php:\n";
            $content = file_get_contents($configPath . '\\services.php');
            // Show only relevant parts
            if (preg_match('/comet|api|rest/i', $content)) {
                echo $content . "\n";
            }
        }
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo str_repeat("═", 80) . "\n";
