<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== MULTI-TENANCY DEBUG ===\n\n";

// Check if tenancy is initialized
echo "1. Tenancy Status:\n";
$tenant = tenancy()->tenant;
if ($tenant) {
    echo "   ✗ PROBLEM: Tenancy ist initialisiert!\n";
    echo "   Tenant ID: {$tenant->id}\n";
    echo "   Tenant Domain: {$tenant->domain}\n";
    echo "   Dies sollte NICHT der Fall sein für /admin!\n\n";
} else {
    echo "   ✓ Tenancy ist NICHT initialisiert (korrekt für Central)\n\n";
}

// Check database connections
echo "2. Database Connections:\n";
echo "   Default Connection: " . config('database.default') . "\n";
echo "   Session Connection: " . config('session.connection') . "\n\n";

// Check if localhost should trigger tenancy
echo "3. Domain Check:\n";
$host = 'localhost';
echo "   Host: {$host}\n";

// Check if subdomain
if (str_contains($host, '.')) {
    $parts = explode('.', $host);
    echo "   Subdomain erkannt: {$parts[0]}\n";
} else {
    echo "   ✓ Keine Subdomain (korrekt für Central)\n";
}

// Check tenancy routes
echo "\n4. Tenancy Route Check:\n";
echo "   /admin sollte CENTRAL Route sein\n";
echo "   *.localhost:8000/club sollte TENANT Route sein\n\n";

// Check User Model Connection
echo "5. User Model Connection:\n";
$userModel = new \App\Models\Central\User();
echo "   User Model Connection: {$userModel->getConnectionName()}\n";
echo "   Sollte 'mysql' sein für Central\n\n";

// Test User Query
echo "6. User Query Test:\n";
try {
    config(['database.default' => 'mysql']);
    $user = \App\Models\Central\User::first();
    if ($user) {
        echo "   ✓ User gefunden: {$user->email}\n";
        echo "   Connection: {$user->getConnectionName()}\n";
        echo "   Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Fehler: " . $e->getMessage() . "\n";
}

echo "\n=== PROBLEM-ANALYSE ===\n";
echo "Wenn bei /admin Tenancy initialisiert wird:\n";
echo "  → Auth sucht User in TENANT-DB (leer!)\n";
echo "  → 403 weil kein User gefunden\n\n";
echo "LÖSUNG: tenancy.php überprüfen!\n";
