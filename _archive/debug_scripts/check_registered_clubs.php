<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking registered clubs/tenants in kpkb3...\n";
echo "==============================================\n\n";

// Check tenants table
echo "1. TENANTS TABLE:\n";
echo "-----------------\n";
$tenants = DB::connection('mysql')->table('kpkb3.tenants')->get();

foreach ($tenants as $tenant) {
    echo "\nTenant ID: {$tenant->id}\n";

    // Get all columns
    foreach ((array)$tenant as $key => $value) {
        if ($key != 'id') {
            echo "  {$key}: {$value}\n";
        }
    }

    // Get domains for this tenant
    $domains = DB::connection('mysql')->table('kpkb3.domains')
        ->where('tenant_id', $tenant->id)
        ->get();

    echo "  Domains:\n";
    foreach ($domains as $domain) {
        echo "    - {$domain->domain}\n";
    }
}

echo "\n\n2. CLUB DATA IN TENANT DATABASES:\n";
echo "----------------------------------\n";

foreach ($tenants as $tenant) {
    echo "\n{$tenant->id}:\n";

    // Initialize tenant
    $domain = DB::connection('mysql')->table('kpkb3.domains')
        ->where('tenant_id', $tenant->id)
        ->first();

    if ($domain) {
        try {
            tenancy()->initialize(
                \Stancl\Tenancy\Database\Models\Tenant::find($tenant->id)
            );

            // Check template_settings
            $settings = DB::table('template_settings')->first();
            if ($settings) {
                echo "  Website: {$settings->website_name}\n";
                echo "  FIFA ID: " . ($settings->club_fifa_id ?? 'NOT SET') . "\n";
            }

            tenancy()->end();
        } catch (\Exception $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
        }
    }
}
