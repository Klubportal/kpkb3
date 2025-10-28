<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get all tenants
$tenants = \App\Models\Tenant::all();

echo "=== MIGRIERE COMET-TABELLEN FÜR ALLE TENANTS ===\n\n";

foreach ($tenants as $tenant) {
    echo "Tenant: {$tenant->id}\n";

    try {
        tenancy()->initialize($tenant);

        // Run only comet migrations
        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        echo "✅ Migration erfolgreich\n";

    } catch (\Exception $e) {
        echo "❌ Fehler: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo "\nFertig!\n";
