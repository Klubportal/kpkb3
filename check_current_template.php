<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Current Template Setting ===\n\n";

try {
    // Get tenant database config
    $tenantDb = 'tenant_nknapijed';

    echo "Connecting to database: {$tenantDb}\n\n";

    // Query template_settings
    $setting = DB::connection('mysql')->table($tenantDb . '.template_settings')->first();

    if ($setting) {
        echo "Current Template Setting (ID: {$setting->id}):\n";
        echo "  - Selected Template: " . ($setting->selected_template ?? 'NULL') . "\n";
        echo "  - Website Name: " . ($setting->website_name ?? 'NULL') . "\n";
        echo "  - Logo: " . ($setting->logo ?? 'NULL') . "\n\n";

        // Check if template exists
        if ($setting->selected_template) {
            $templatePath = __DIR__ . '/resources/views/templates/' . $setting->selected_template;
            if (is_dir($templatePath)) {
                echo "✓ Template directory exists: {$templatePath}\n";

                $homeFile = $templatePath . '/home.blade.php';
                if (file_exists($homeFile)) {
                    echo "✓ home.blade.php exists\n";
                    echo "  File size: " . filesize($homeFile) . " bytes\n";
                } else {
                    echo "✗ home.blade.php NOT FOUND\n";
                }
            } else {
                echo "✗ Template directory NOT FOUND: {$templatePath}\n";
            }
        } else {
            echo "No template selected (selected_template is NULL)\n";
        }
    } else {
        echo "No template_settings record found!\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
