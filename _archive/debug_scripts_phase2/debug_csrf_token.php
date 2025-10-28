<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 DEBUG CSRF & SESSION\n";
echo str_repeat("=", 60) . "\n\n";

// Simuliere Tenant Request
$_SERVER['HTTP_HOST'] = 'testclub.localhost';
$_SERVER['REQUEST_URI'] = '/club/login';

// Tenancy initialisieren
$tenant = \App\Models\Central\Tenant::where('id', 'testclub')->first();
tenancy()->initialize($tenant);

echo "✅ Tenancy: " . tenant('id') . "\n\n";

// Middleware simulieren (wie in SetTenantSessionConnection)
config([
    'session.driver' => 'file',
    'session.files' => storage_path('framework/sessions'),
]);

app()->forgetInstance('session');
app()->forgetInstance('session.store');

echo "📋 SESSION CONFIG:\n";
echo "   Driver: " . config('session.driver') . "\n";
echo "   Files: " . config('session.files') . "\n";
echo "   Cookie: " . config('session.cookie') . "\n";
echo "   Domain: " . (config('session.domain') ?: '(null)') . "\n";
echo "   Path: " . config('session.path') . "\n";
echo "   Same Site: " . config('session.same_site') . "\n\n";

// Session Manager holen
$session = app('session')->driver();

// Session ID setzen
$sessionId = 'test_' . time();
$session->setId($sessionId);
$session->start();

echo "🔑 SESSION:\n";
echo "   ID: " . $session->getId() . "\n";
echo "   Name: " . $session->getName() . "\n\n";

// CSRF Token generieren
$token = $session->token();
echo "🔐 CSRF TOKEN (beim Laden der Seite):\n";
echo "   Token: " . $token . "\n\n";

// Session speichern
$session->save();

echo "💾 Session gespeichert in:\n";
$sessionFile = storage_path('framework/sessions/' . $sessionId);
echo "   $sessionFile\n";
if (file_exists($sessionFile)) {
    echo "   ✅ Datei existiert!\n";
    $content = file_get_contents($sessionFile);
    echo "   Größe: " . strlen($content) . " bytes\n";

    // Session-Daten anzeigen
    $data = unserialize($content);
    echo "\n📦 SESSION DATEN:\n";
    print_r($data);
} else {
    echo "   ❌ Datei NICHT gefunden!\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🧪 SIMULIERE POST REQUEST (Login-Versuch):\n\n";

// Neue Session-Instanz (wie bei neuem Request)
app()->forgetInstance('session');
app()->forgetInstance('session.store');
$newSession = app('session')->driver();
$newSession->setId($sessionId);
$newSession->start();

$retrievedToken = $newSession->token();
echo "   Retrieved Token: " . $retrievedToken . "\n";
echo "   Original Token:  " . $token . "\n";
echo "\n   Match: " . ($retrievedToken === $token ? "✅ JA (gut!)" : "❌ NEIN (PROBLEM!)") . "\n";

if ($retrievedToken !== $token) {
    echo "\n❌ FEHLER: Token stimmt nicht überein!\n";
    echo "   → Das ist warum 419 kommt!\n";
} else {
    echo "\n✅ Token ist korrekt!\n";
    echo "   → 419 muss einen anderen Grund haben\n";
}
