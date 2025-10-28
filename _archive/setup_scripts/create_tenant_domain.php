<?php

/**
 * Temporäres Script zum Erstellen von Tenant-Domains
 * Ausführen mit: php create_tenant_domain.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Central\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

echo "\n========================================\n";
echo "   TENANT DOMAIN ERSTELLEN\n";
echo "========================================\n\n";

// Tenant holen
$tenant = Tenant::first();

if (!$tenant) {
    echo "❌ Kein Tenant gefunden!\n";
    echo "Bitte erst einen Tenant erstellen.\n\n";
    exit(1);
}

echo "✅ Tenant gefunden:\n";
echo "   ID: {$tenant->id}\n";
echo "   Plan: {$tenant->plan_id}\n\n";

// Existierende Domains anzeigen
echo "Aktuelle Domains:\n";
$existingDomains = $tenant->domains()->get();

if ($existingDomains->isEmpty()) {
    echo "   (keine)\n\n";
} else {
    foreach ($existingDomains as $domain) {
        echo "   - {$domain->domain}\n";
    }
    echo "\n";
}

// Neue Domain erstellen
$newDomain = 'testclub.localhost';

// Prüfen ob Domain schon existiert
$exists = Domain::where('domain', $newDomain)->exists();

if ($exists) {
    echo "ℹ️  Domain '{$newDomain}' existiert bereits!\n\n";
} else {
    // Domain erstellen
    $domain = $tenant->domains()->create([
        'domain' => $newDomain,
    ]);

    echo "✅ Domain '{$newDomain}' erfolgreich erstellt!\n\n";
}

echo "========================================\n";
echo "   NÄCHSTE SCHRITTE\n";
echo "========================================\n\n";
echo "1. hosts-Datei bearbeiten:\n";
echo "   C:\\Windows\\System32\\drivers\\etc\\hosts\n";
echo "   Zeile hinzufügen: 127.0.0.1 testclub.localhost\n\n";
echo "2. Panel-Provider werden automatisch aktualisiert\n\n";
echo "3. Server neu starten:\n";
echo "   php artisan serve\n\n";
echo "4. Testen:\n";
echo "   http://testclub.localhost:8000/club\n\n";

