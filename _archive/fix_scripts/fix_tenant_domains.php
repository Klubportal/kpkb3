<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

echo "=========================================\n";
echo "  TENANT DOMAIN CHECKER & FIXER\n";
echo "=========================================\n\n";

try {
    // Load Laravel environment
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    // Prüfe alle Tenants ohne Domain-Einträge
    $tenantsWithoutDomains = DB::connection('central')
        ->table('tenants')
        ->leftJoin('domains', 'tenants.id', '=', 'domains.tenant_id')
        ->whereNull('domains.domain')
        ->select('tenants.id', 'tenants.name')
        ->get();

    if ($tenantsWithoutDomains->isEmpty()) {
        echo "✓ Alle Tenants haben korrekte Domain-Einträge!\n\n";
    } else {
        echo "⚠ Gefundene Tenants ohne Domain-Einträge:\n\n";

        foreach ($tenantsWithoutDomains as $tenant) {
            echo "Tenant ID: {$tenant->id}\n";
            echo "Name: {$tenant->name}\n";

            // Automatisch Domain hinzufügen
            $domain = $tenant->id . '.localhost';

            try {
                DB::connection('central')->table('domains')->insert([
                    'domain' => $domain,
                    'tenant_id' => $tenant->id
                ]);

                echo "✓ Domain hinzugefügt: {$domain}\n";
            } catch (Exception $e) {
                echo "✗ Fehler beim Hinzufügen der Domain: " . $e->getMessage() . "\n";
            }

            echo "\n";
        }

        echo "Cache wird geleert...\n";
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        echo "✓ Cache geleert\n\n";
    }

    // Zeige alle aktuellen Tenant-Domain-Zuordnungen
    echo "Aktuelle Tenant-Domain-Zuordnungen:\n";
    echo "===================================\n";

    $allTenants = DB::connection('central')
        ->table('tenants')
        ->leftJoin('domains', 'tenants.id', '=', 'domains.tenant_id')
        ->select('tenants.id', 'tenants.name', 'domains.domain')
        ->orderBy('tenants.id')
        ->get();

    foreach ($allTenants as $tenant) {
        $domain = $tenant->domain ?? 'KEINE DOMAIN';
        echo "ID: {$tenant->id} | Name: {$tenant->name} | Domain: {$domain}\n";
    }

    echo "\n✓ Prüfung abgeschlossen!\n";

} catch (Exception $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
    exit(1);
}
