<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

echo "Adding club_fifa_id column to template_settings table for all tenants...\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "Processing tenant: {$tenant->id}\n";

    $tenant->run(function () use ($tenant) {
        try {
            // Check if column already exists
            $exists = DB::select("SHOW COLUMNS FROM template_settings LIKE 'club_fifa_id'");

            if (empty($exists)) {
                // Add the column
                DB::statement("ALTER TABLE template_settings ADD COLUMN club_fifa_id INT NULL AFTER website_name");
                echo "  ✓ Column club_fifa_id added successfully\n";
            } else {
                echo "  - Column club_fifa_id already exists\n";
            }
        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    });
}

echo "\n✓ Done!\n";
