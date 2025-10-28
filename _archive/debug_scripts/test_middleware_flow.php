<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== MIDDLEWARE TEST (simuliert Browser-Request) ===\n\n";

// Simuliere Request zu testclub.localhost
$_SERVER['HTTP_HOST'] = 'testclub.localhost';
$_SERVER['REQUEST_URI'] = '/club/login';
$_SERVER['SERVER_NAME'] = 'testclub.localhost';

// Tenancy sollte automatisch initialisiert werden
echo "🌐 Request Host: testclub.localhost\n";
echo "📍 Request URI: /club/login\n\n";

// Prüfe ob Tenancy initialisiert wird
if (tenancy()->initialized) {
    echo "✅ Tenancy INITIALISIERT\n";
    echo "   Tenant: " . tenant('id') . "\n\n";
} else {
    echo "❌ Tenancy NICHT initialisiert\n\n";
}

// Prüfe Session-Config
echo "🔧 Session Config:\n";
echo "   driver: " . config('session.driver') . "\n";
echo "   connection: " . config('session.connection') . "\n";
echo "   domain: " . config('session.domain') . "\n\n";

// Teste ob SetTenantSessionConnection Middleware existiert
echo "🔍 Middleware Check:\n";
if (class_exists(\App\Http\Middleware\SetTenantSessionConnection::class)) {
    echo "   ✅ SetTenantSessionConnection exists\n";

    // Simuliere Middleware-Ausführung
    $middleware = new \App\Http\Middleware\SetTenantSessionConnection();
    $request = \Illuminate\Http\Request::create('http://testclub.localhost/club/login');

    try {
        $middleware->handle($request, function($req) {
            echo "   ✅ Middleware executed\n";
            return new \Illuminate\Http\Response('OK');
        });

        echo "   Session connection NACH Middleware: " . config('session.connection') . "\n";
    } catch (\Exception $e) {
        echo "   ❌ Middleware ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ SetTenantSessionConnection NOT FOUND!\n";
}

echo "\n";
