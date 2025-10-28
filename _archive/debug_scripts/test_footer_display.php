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

echo "Testing template_settings data loading...\n";
echo "==========================================\n\n";

// Get settings using the model
$settings = \App\Models\Tenant\TemplateSetting::first();

if ($settings) {
    echo "✓ Settings loaded successfully\n\n";

    echo "Website Name: " . ($settings->website_name ?? 'NULL') . "\n";
    echo "Slogan: " . ($settings->slogan ?? 'NULL') . "\n";
    echo "Logo: " . ($settings->logo ?? 'NULL') . "\n";
    echo "Footer About: " . ($settings->footer_about ?? 'NULL') . "\n";
    echo "\nFooter About length: " . strlen($settings->footer_about ?? '') . " characters\n";

    if (!empty($settings->footer_about)) {
        echo "\n✓ footer_about has content and should be displayed\n";
    } else {
        echo "\n✗ footer_about is empty or null\n";
    }
} else {
    echo "✗ No settings found\n";
}
