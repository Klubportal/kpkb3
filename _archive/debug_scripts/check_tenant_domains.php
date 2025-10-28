<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

config(['database.default' => 'mysql']);

echo "=== TENANTS & DOMAINS CHECK ===\n\n";

// Get all tenants
$tenants = \App\Models\Central\Tenant::with('domains')->get();

echo "Total Tenants: " . $tenants->count() . "\n\n";

foreach ($tenants as $tenant) {
    echo "Tenant: {$tenant->id}\n";
    echo "  Domains:\n";

    foreach ($tenant->domains as $domain) {
        echo "    - {$domain->domain}\n";

        if ($domain->domain === 'localhost') {
            echo "      ❌ PROBLEM: 'localhost' als Tenant-Domain!\n";
            echo "      Dies wird ALLE localhost-Requests als Tenant behandeln!\n";
        }
    }

    echo "\n";
}

echo "=== LÖSUNG ===\n";
echo "Wenn ein Tenant die Domain 'localhost' hat:\n";
echo "  → Alle Requests an localhost:8000 initialisieren Tenancy\n";
echo "  → /admin wird als Tenant-Route behandelt!\n\n";
echo "LÖSUNG: Domain von 'localhost' auf 'testclub.localhost' ändern\n";
