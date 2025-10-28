<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SIMULATING TENANT LOGIN REQUEST ===" . PHP_EOL . PHP_EOL;

// Simulate domain-based tenant resolution
$domain = 'testclub.localhost';
echo "Domain: " . $domain . PHP_EOL;

$tenantDomain = DB::connection('central')->table('domains')->where('domain', $domain)->first();

if (!$tenantDomain) {
    echo "❌ Domain '$domain' NOT FOUND in domains table!" . PHP_EOL;

    // List all domains
    echo PHP_EOL . "Available domains:" . PHP_EOL;
    $domains = DB::connection('central')->table('domains')->get();
    foreach ($domains as $d) {
        echo "  - " . $d->domain . " (tenant: " . $d->tenant_id . ")" . PHP_EOL;
    }
    exit(1);
}

echo "✅ Domain found! Tenant ID: " . $tenantDomain->tenant_id . PHP_EOL . PHP_EOL;

// Load tenant
$tenant = App\Models\Central\Tenant::find($tenantDomain->tenant_id);

if (!$tenant) {
    echo "❌ Tenant NOT FOUND!" . PHP_EOL;
    exit(1);
}

echo "=== BEFORE TENANCY INITIALIZATION ===" . PHP_EOL;
echo "Default DB Connection: " . config('database.default') . PHP_EOL;
echo "Session Connection: " . config('session.connection') . PHP_EOL;
echo "Session Table: " . config('session.table') . PHP_EOL . PHP_EOL;

// Initialize tenancy (what InitializeTenancyByDomain middleware does)
tenancy()->initialize($tenant);

echo "=== AFTER TENANCY INITIALIZATION ===" . PHP_EOL;
echo "Tenancy Initialized: " . (tenancy()->initialized ? 'YES' : 'NO') . PHP_EOL;
echo "Current Tenant ID: " . tenant('id') . PHP_EOL;
echo "Tenant DB: " . config('database.connections.tenant.database') . PHP_EOL;
echo "Session Connection (from config): " . config('session.connection') . PHP_EOL;
echo "Session Table: " . config('session.table') . PHP_EOL . PHP_EOL;

// Now simulate what SetTenantSessionConnection middleware should do
echo "=== SIMULATING SetTenantSessionConnection MIDDLEWARE ===" . PHP_EOL;

if (tenancy()->initialized) {
    echo "✅ Tenancy is initialized, switching session connection..." . PHP_EOL;

    config([
        'session.connection' => 'tenant',
        'session.table' => 'sessions',
    ]);

    echo "Session Connection (after middleware): " . config('session.connection') . PHP_EOL;
    echo "Session Table (after middleware): " . config('session.table') . PHP_EOL . PHP_EOL;
}

// Test actual database connections
echo "=== DATABASE CONNECTION TEST ===" . PHP_EOL;

try {
    // Central DB
    $centralUsers = DB::connection('central')->table('users')->count();
    echo "Central DB Users: " . $centralUsers . PHP_EOL;

    // Tenant DB
    $tenantUsers = DB::connection('tenant')->table('users')->count();
    echo "Tenant DB Users: " . $tenantUsers . PHP_EOL;

    // Check tenant user
    $tenantUser = DB::connection('tenant')->table('users')
        ->where('email', 'admin@testclub.com')
        ->first();

    if ($tenantUser) {
        echo "✅ User 'admin@testclub.com' found in TENANT DB!" . PHP_EOL;
        echo "   ID: " . $tenantUser->id . PHP_EOL;
        echo "   Name: " . $tenantUser->name . PHP_EOL;

        if (Hash::check('Zagreb123!', $tenantUser->password)) {
            echo "   Password: ✅ CORRECT (Zagreb123!)" . PHP_EOL;
        } else {
            echo "   Password: ❌ INCORRECT" . PHP_EOL;
        }
    } else {
        echo "❌ User 'admin@testclub.com' NOT FOUND in tenant DB!" . PHP_EOL;
    }

    // Check if user exists in central DB (should NOT!)
    $centralUser = DB::connection('central')->table('users')
        ->where('email', 'admin@testclub.com')
        ->first();

    if ($centralUser) {
        echo "⚠️  WARNING: User also exists in CENTRAL DB (wrong!)" . PHP_EOL;
    } else {
        echo "✅ User does NOT exist in central DB (correct!)" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== AUTH GUARD CONFIGURATION ===" . PHP_EOL;
echo "Default Guard: " . config('auth.defaults.guard') . PHP_EOL;
echo "Tenant Guard Provider: " . config('auth.guards.tenant.provider') . PHP_EOL;
echo "Tenant Provider Model: " . config('auth.providers.tenants.model') . PHP_EOL;

// Check which connection the tenant provider uses
$tenantModel = config('auth.providers.tenants.model');
if (class_exists($tenantModel)) {
    $testModel = new $tenantModel;
    echo "Tenant Model Connection: " . ($testModel->getConnectionName() ?: 'default') . PHP_EOL;
}
