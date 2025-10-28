<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SESSION CONFIG DEBUG ===\n\n";

echo "📋 .env Settings:\n";
echo "   SESSION_DRIVER: " . env('SESSION_DRIVER') . "\n";
echo "   SESSION_CONNECTION: " . env('SESSION_CONNECTION') . "\n";
echo "   SESSION_DOMAIN: " . env('SESSION_DOMAIN') . "\n";
echo "   SESSION_SAME_SITE: " . env('SESSION_SAME_SITE') . "\n";
echo "   SESSION_PATH: " . env('SESSION_PATH') . "\n\n";

echo "🔧 Config (nach Bootstrap):\n";
echo "   session.driver: " . config('session.driver') . "\n";
echo "   session.connection: " . config('session.connection') . "\n";
echo "   session.domain: " . config('session.domain') . "\n";
echo "   session.same_site: " . config('session.same_site') . "\n";
echo "   session.path: " . config('session.path') . "\n\n";

echo "🌐 Tenancy Status:\n";
echo "   Initialized: " . (tenancy()->initialized ? 'YES' : 'NO') . "\n";

if (tenancy()->initialized) {
    echo "   Tenant ID: " . tenant('id') . "\n";
    echo "   Tenant DB: " . config('database.connections.tenant.database') . "\n";
} else {
    echo "   Context: CENTRAL\n";
}

echo "\n🔍 Session Store Test:\n";
try {
    $store = app('session.store');
    echo "   Store Class: " . get_class($store) . "\n";
    echo "   Driver: " . $store->getHandler()::class . "\n";

    // Teste Session-Schreiben
    session()->put('test_key', 'test_value_' . time());
    session()->save();

    $testValue = session('test_key');
    echo "   Write/Read Test: " . ($testValue ? "✅ OK ({$testValue})" : "❌ FAILED") . "\n";
} catch (\Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";
