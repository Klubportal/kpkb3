<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DOMAIN IDENTIFICATION TEST ===\n\n";

// Simuliere Request zu localhost
$host = 'localhost';
echo "Testing Host: {$host}\n\n";

// Check if domain exists in database
config(['database.default' => 'mysql']);

$domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $host)->first();

if ($domain) {
    echo "❌ PROBLEM GEFUNDEN!\n";
    echo "Domain '{$host}' existiert in der domains Tabelle!\n";
    echo "Tenant ID: {$domain->tenant_id}\n\n";
    echo "LÖSUNG: Diese Domain muss gelöscht werden!\n";
    echo "DELETE FROM domains WHERE domain = 'localhost';\n\n";

    $domain->delete();
    echo "✓ Domain '{$host}' wurde gelöscht!\n";
} else {
    echo "✓ Domain '{$host}' existiert NICHT in der Datenbank (korrekt)\n";
}

echo "\n=== ALLE DOMAINS ===\n";
$allDomains = \Stancl\Tenancy\Database\Models\Domain::all();
foreach ($allDomains as $d) {
    echo "  - {$d->domain} → Tenant: {$d->tenant_id}\n";
}
