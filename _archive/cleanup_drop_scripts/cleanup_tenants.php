<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "  TENANT DATENBANK BEREINIGUNG\n";
echo "========================================\n\n";

// Zeige alle Tenants
echo "Vorhandene Tenants:\n";
echo "-------------------\n";
$tenants = DB::connection('central')->table('tenants')->get(['id', 'name', 'email']);
foreach ($tenants as $tenant) {
    echo "- ID: {$tenant->id} | Name: {$tenant->name} | Email: {$tenant->email}\n";
}

// Zeige alle Domains
echo "\nVorhandene Domains:\n";
echo "-------------------\n";
$domains = DB::connection('central')->table('domains')->get(['domain', 'tenant_id']);
foreach ($domains as $domain) {
    echo "- Domain: {$domain->domain} | Tenant: {$domain->tenant_id}\n";
}

// Lösche alle Tenants und Domains
echo "\n\nLÖSCHE ALLE TENANTS UND DOMAINS...\n";

DB::connection('central')->table('domains')->delete();
echo "✓ Domains gelöscht\n";

$tenants = DB::connection('central')->table('tenants')->get();
foreach ($tenants as $tenant) {
    // Lösche Tenant-Datenbank
    $dbName = "tenant_{$tenant->id}";
    try {
        DB::connection('central')->statement("DROP DATABASE IF EXISTS `{$dbName}`");
        echo "✓ Datenbank {$dbName} gelöscht\n";
    } catch (\Exception $e) {
        echo "⚠ Konnte {$dbName} nicht löschen: " . $e->getMessage() . "\n";
    }
}

DB::connection('central')->table('tenants')->delete();
echo "✓ Tenants gelöscht\n";

echo "\n========================================\n";
echo "  BEREINIGUNG ABGESCHLOSSEN!\n";
echo "========================================\n";
echo "\nSie können jetzt einen neuen Tenant registrieren:\n";
echo "http://localhost:8000/register\n\n";
