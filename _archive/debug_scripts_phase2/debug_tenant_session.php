<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TENANT SESSION CONFIG DEBUG ===\n\n";

// Initialisiere Tenant
$tenant = \App\Models\Central\Tenant::find('testclub');
if (!$tenant) {
    echo "❌ Tenant 'testclub' nicht gefunden!\n";
    exit(1);
}

tenancy()->initialize($tenant);
echo "✅ Tenancy initialisiert: {$tenant->id}\n\n";

echo "📋 .env Settings:\n";
echo "   SESSION_DRIVER: " . env('SESSION_DRIVER') . "\n";
echo "   SESSION_CONNECTION: " . env('SESSION_CONNECTION') . "\n";
echo "   SESSION_DOMAIN: " . env('SESSION_DOMAIN') . "\n\n";

echo "🔧 Config (SOLLTE tenant sein!):\n";
echo "   session.driver: " . config('session.driver') . "\n";
echo "   session.connection: " . config('session.connection') . "\n";
echo "   session.domain: " . config('session.domain') . "\n\n";

echo "🌐 Tenancy Status:\n";
echo "   Initialized: " . (tenancy()->initialized ? 'YES' : 'NO') . "\n";
echo "   Tenant ID: " . tenant('id') . "\n";
echo "   Tenant DB: " . config('database.connections.tenant.database') . "\n\n";

echo "🔍 Session Store Test:\n";
try {
    // Force neu erstellen um dynamic config zu triggern
    app()->forgetInstance('session.store');
    $store = app('session.store');

    echo "   Store Class: " . get_class($store) . "\n";
    echo "   Driver: " . $store->getHandler()::class . "\n";

    // Prüfe in welcher DB die Session landet
    $connectionName = config('session.connection');
    echo "   Session Connection: {$connectionName}\n";

    $dbName = \DB::connection($connectionName)->getDatabaseName();
    echo "   Actual Database: {$dbName}\n";

    // Test Session Write
    session()->put('tenant_test', 'tenant_value_' . time());
    session()->save();

    $testValue = session('tenant_test');
    echo "   Write/Read Test: " . ($testValue ? "✅ OK ({$testValue})" : "❌ FAILED") . "\n";

    // Zähle Sessions in Tenant DB
    $sessionCount = \DB::connection('tenant')->table('sessions')->count();
    echo "   Sessions in tenant DB: {$sessionCount}\n";

} catch (\Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
