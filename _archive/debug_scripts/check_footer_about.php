<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
$domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', 'nknapijed.localhost')->first();
if ($domain) {
    tenancy()->initialize($domain->tenant);
}

echo "Checking template_settings structure...\n";
echo "========================================\n\n";

$columns = DB::select('DESCRIBE template_settings');
$existingColumns = [];

foreach ($columns as $column) {
    $existingColumns[] = $column->Field;
}

echo "Looking for 'footer_about' column...\n";

if (in_array('footer_about', $existingColumns)) {
    echo "✓ footer_about column exists\n";

    // Show current value
    $setting = DB::table('template_settings')->first();
    if ($setting) {
        echo "\nCurrent value: " . ($setting->footer_about ?? 'NULL') . "\n";
    }
} else {
    echo "✗ footer_about column MISSING\n";
    echo "\nAdding footer_about column...\n";

    DB::statement("ALTER TABLE template_settings ADD COLUMN `footer_about` TEXT NULL AFTER `slogan`");

    echo "✓ footer_about column added successfully!\n";
}
