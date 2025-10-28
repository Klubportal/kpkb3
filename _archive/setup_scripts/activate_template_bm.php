<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get the tenant
$tenant = \Stancl\Tenancy\Database\Models\Tenant::find('nknapijed');

if (!$tenant) {
    echo "Tenant 'nknapijed' not found!\n";
    exit(1);
}

echo "Current template: " . ($tenant->template ?? 'not set') . "\n";

// Update template to 'bm'
$tenant->template = 'bm';
$tenant->save();

echo "✓ Updated template to: bm\n";
echo "\nTemplate 'bm' is now active for nknapijed tenant.\n";
echo "Visit: http://nknapijed.localhost:8000\n";
