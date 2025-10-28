<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Stancl\Tenancy\Database\Models\Domain;

echo "\n========================================\n";
echo "   TENANT DOMAIN TEST\n";
echo "========================================\n\n";

// Teste ob Domain gefunden wird
$domain = Domain::where('domain', 'testclub.localhost')->first();

if ($domain) {
    echo "✅ Domain gefunden: {$domain->domain}\n";
    echo "   Tenant ID: {$domain->tenant_id}\n";

    $tenant = $domain->tenant;
    if ($tenant) {
        echo "✅ Tenant gefunden: {$tenant->id}\n";
        echo "   Tenant Database: tenant_{$tenant->id}\n";

        // Initialisiere Tenancy
        tenancy()->initialize($tenant);
        echo "✅ Tenancy initialisiert!\n\n";

        // Teste DB Connection
        $currentDB = DB::connection()->getDatabaseName();
        echo "Aktuelle Datenbank: {$currentDB}\n\n";

        // Zähle Users in Tenant DB
        $userCount = DB::table('users')->count();
        echo "Users in Tenant DB: {$userCount}\n";

    } else {
        echo "❌ Tenant NICHT gefunden für ID: {$domain->tenant_id}\n";
    }
} else {
    echo "❌ Domain 'testclub.localhost' NICHT gefunden!\n";
    echo "\nVerfügbare Domains:\n";
    Domain::all()->each(function($d) {
        echo "  - {$d->domain} → {$d->tenant_id}\n";
    });
}

echo "\n========================================\n\n";
