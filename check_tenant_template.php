<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Tenant Template ===\n\n";

try {
    // Get tenant info
    $tenant = DB::connection('mysql')->table('tenants')->where('id', 'nknapijed')->first();

    if ($tenant) {
        echo "Tenant: {$tenant->id}\n";
        echo "Template ID: " . ($tenant->template_id ?? 'NULL') . "\n\n";

        if ($tenant->template_id) {
            $template = DB::connection('mysql')->table('templates')->where('id', $tenant->template_id)->first();
            if ($template) {
                echo "Template Info:\n";
                echo "  - Name: {$template->name}\n";
                echo "  - Slug: {$template->slug}\n";
                echo "  - Is Active: " . ($template->is_active ? 'Yes' : 'No') . "\n\n";

                // Check if template files exist
                $templatePath = __DIR__ . '/resources/views/templates/' . $template->slug;
                if (is_dir($templatePath)) {
                    echo "✓ Template directory exists: {$templatePath}\n";
                    $homeFile = $templatePath . '/home.blade.php';
                    if (file_exists($homeFile)) {
                        echo "✓ home.blade.php exists\n";
                    } else {
                        echo "✗ home.blade.php NOT FOUND\n";
                    }
                } else {
                    echo "✗ Template directory NOT FOUND\n";
                }
            } else {
                echo "✗ Template #{$tenant->template_id} not found in database!\n";
            }
        } else {
            echo "No template assigned (will use default 'kp')\n";
        }
    } else {
        echo "Tenant 'nknapijed' not found!\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
