<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 TESTE SESSION-DATENBANK\n";
echo str_repeat("=", 60) . "\n\n";

// Simuliere Tenant-Request
$_SERVER['HTTP_HOST'] = 'testclub.localhost';

// Tenancy manuell initialisieren
$tenant = \App\Models\Central\Tenant::where('id', 'testclub')->first();
if (!$tenant) {
    die("❌ Tenant 'testclub' nicht gefunden!\n");
}

tenancy()->initialize($tenant);

echo "✅ Tenancy initialized: " . (tenancy()->initialized ? 'YES' : 'NO') . "\n";
echo "   Tenant ID: " . tenant('id') . "\n\n";

// Middleware simulieren
echo "🔧 SIMULIERE SetTenantSessionConnection Middleware:\n";
if (tenancy()->initialized) {
    echo "   - Setting session.connection to 'tenant'\n";
    config(['session.connection' => 'tenant']);
    app()->forgetInstance('session.store');
    echo "   - Session store forgotten and will be recreated\n";
}
echo "\n";

// Session-Config prüfen
echo "📋 SESSION CONFIG:\n";
echo "   Driver: " . config('session.driver') . "\n";
echo "   Connection: " . config('session.connection') . "\n";
echo "   Table: " . config('session.table') . "\n\n";

// Session Store erstellen
echo "🏗️  ERSTELLE SESSION STORE:\n";
$manager = app('session');
$store = $manager->driver();

echo "   Store Class: " . get_class($store) . "\n";

// Reflection nutzen um an die Connection zu kommen
if (method_exists($store, 'getHandler')) {
    $handler = $store->getHandler();
    echo "   Handler Class: " . get_class($handler) . "\n";

    if (method_exists($handler, 'getConnection')) {
        $reflection = new ReflectionMethod($handler, 'getConnection');
        $reflection->setAccessible(true);
        $connection = $reflection->invoke($handler);
        echo "   Connection Name: " . $connection->getName() . "\n";

        // Datenbank-Name ermitteln
        $dbName = $connection->getDatabaseName();
        echo "   Database Name: " . $dbName . "\n";
    }
}
echo "\n";

// Test: Session schreiben und lesen
echo "✍️  TEST: Session schreiben:\n";
$sessionId = 'test_' . time();
$store->setId($sessionId);
$store->put('test_key', 'test_value_' . time());
$store->save();
echo "   Session ID: " . $sessionId . "\n";
echo "   Geschrieben: test_key = test_value\n\n";

// Prüfen in welcher DB die Session gelandet ist
echo "🔍 PRÜFE WO SESSION GESPEICHERT WURDE:\n\n";

// Central DB prüfen
$centralSession = DB::connection('central')
    ->table('sessions')
    ->where('id', $sessionId)
    ->first();

echo "   Central DB (kpkb3):\n";
echo "   " . ($centralSession ? "✅ GEFUNDEN! (FALSCH!)" : "❌ Nicht gefunden") . "\n\n";

// Tenant DB prüfen
$tenantSession = DB::connection('tenant')
    ->table('sessions')
    ->where('id', $sessionId)
    ->first();

echo "   Tenant DB (tenant_testclub):\n";
echo "   " . ($tenantSession ? "✅ GEFUNDEN! (RICHTIG!)" : "❌ Nicht gefunden (PROBLEM!)") . "\n\n";

echo str_repeat("=", 60) . "\n";
echo "📊 DIAGNOSE:\n";
if ($centralSession && !$tenantSession) {
    echo "❌ FEHLER: Session landet in Central DB statt Tenant DB!\n";
    echo "   → Middleware funktioniert NICHT richtig!\n";
} elseif (!$centralSession && $tenantSession) {
    echo "✅ PERFEKT: Session ist in Tenant DB!\n";
    echo "   → Middleware funktioniert!\n";
} elseif ($centralSession && $tenantSession) {
    echo "⚠️  WARNUNG: Session in BEIDEN Datenbanken!\n";
} else {
    echo "❌ ERROR: Session in KEINER Datenbank!\n";
}
