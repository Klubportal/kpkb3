<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 DEBUG SESSION HANDLER CONNECTION\n";
echo str_repeat("=", 60) . "\n\n";

// Tenancy initialisieren
$_SERVER['HTTP_HOST'] = 'testclub.localhost';
$tenant = \App\Models\Central\Tenant::where('id', 'testclub')->first();
tenancy()->initialize($tenant);

echo "✅ Tenancy: " . tenant('id') . "\n\n";

// Config setzen
config(['session.connection' => 'tenant']);
echo "📋 session.connection = " . config('session.connection') . "\n\n";

// Session Manager holen
$sessionManager = app('session');

// Driver erstellen
$driver = $sessionManager->driver('database');
$handler = $driver->getHandler();

echo "🔧 Handler Class: " . get_class($handler) . "\n\n";

// Connection Name mittels Reflection auslesen
$reflection = new ReflectionClass($handler);
$connectionProperty = $reflection->getProperty('connection');
$connectionProperty->setAccessible(true);

$connectionInstance = $connectionProperty->getValue($handler);

if ($connectionInstance) {
    echo "📌 Handler Connection Name: " . $connectionInstance->getName() . "\n";
    echo "📌 Handler Database Name: " . $connectionInstance->getDatabaseName() . "\n\n";
} else {
    echo "❌ Connection ist NULL!\n\n";
}

// Jetzt Session-Tabelle direkt abfragen
echo "🔍 Prüfe sessions-Tabelle:\n\n";

try {
    $centralCount = DB::connection('central')->table('sessions')->count();
    echo "   Central DB: $centralCount sessions\n";
} catch (Exception $e) {
    echo "   Central DB: ERROR - " . $e->getMessage() . "\n";
}

try {
    $tenantCount = DB::connection('tenant')->table('sessions')->count();
    echo "   Tenant DB: $tenantCount sessions\n";
} catch (Exception $e) {
    echo "   Tenant DB: ERROR - " . $e->getMessage() . "\n";
}
