<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Disabling Template Temporarily ===\n\n";

try {
    $tenantDb = 'tenant_nknapijed';

    echo "Setting selected_template to NULL in {$tenantDb}.template_settings...\n";

    $updated = DB::connection('mysql')->table($tenantDb . '.template_settings')
        ->where('id', 1)
        ->update(['selected_template' => null]);

    if ($updated) {
        echo "✓ Template disabled successfully!\n";
        echo "  You can now access the website at: http://nknaprijed.localhost:8000\n";
        echo "  And the backend at: http://nknaprijed.localhost:8000/club\n\n";
        echo "To re-enable the template, set selected_template back to 'fcbm' in the database.\n";
    } else {
        echo "No changes made (template might already be NULL).\n";
    }

    // Verify
    $setting = DB::connection('mysql')->table($tenantDb . '.template_settings')->first();
    echo "\nCurrent value: selected_template = " . ($setting->selected_template ?? 'NULL') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
