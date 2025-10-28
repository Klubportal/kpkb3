<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Force Central Connection
config(['database.default' => 'mysql']);

echo "=== AUTHENTICATION TEST ===\n\n";

// Test User Model
echo "1. User Model Test:\n";
$user = \App\Models\Central\User::first();
if ($user) {
    echo "   ✓ User gefunden: {$user->email}\n";
    echo "   ✓ Name: {$user->name}\n";
    echo "   ✓ ID: {$user->id}\n";
} else {
    echo "   ✗ Kein User gefunden!\n";
}

echo "\n2. canAccessPanel Test:\n";
if ($user) {
    // Create a mock panel
    $panelConfig = [
        'id' => 'central',
        'path' => 'admin',
    ];

    echo "   Testing method existence...\n";
    if (method_exists($user, 'canAccessPanel')) {
        echo "   ✓ canAccessPanel Methode existiert\n";

        // Create a simple mock panel
        $panel = new class($panelConfig) {
            private $id;
            public function __construct($config) {
                $this->id = $config['id'];
            }
            public function getId() {
                return $this->id;
            }
        };

        $canAccess = $user->canAccessPanel($panel);
        echo "   " . ($canAccess ? "✓" : "✗") . " canAccessPanel('central') = " . ($canAccess ? 'true' : 'false') . "\n";
    } else {
        echo "   ✗ canAccessPanel Methode NICHT gefunden!\n";
    }
}

echo "\n3. Auth Config:\n";
echo "   Default Guard: " . config('auth.defaults.guard') . "\n";
echo "   Web Provider: " . config('auth.guards.web.provider') . "\n";
echo "   Provider Model: " . config('auth.providers.users.model') . "\n";

echo "\n4. Password Hash Test:\n";
if ($user) {
    echo "   Current hash: " . substr($user->password, 0, 20) . "...\n";
    $testPassword = 'Zagreb123!';
    echo "   Testing password: {$testPassword}\n";

    if (\Illuminate\Support\Facades\Hash::check($testPassword, $user->password)) {
        echo "   ✓ Password STIMMT!\n";
    } else {
        echo "   ✗ Password stimmt NICHT\n";
        echo "   Neues Password wird gesetzt...\n";
        $user->password = \Illuminate\Support\Facades\Hash::make($testPassword);
        $user->save();
        echo "   ✓ Password wurde auf '{$testPassword}' gesetzt\n";
    }
}

echo "\n=== ZUGRIFF TESTEN ===\n";
echo "URL: http://localhost:8000/admin/login\n";
echo "Email: {$user->email}\n";
echo "Password: Zagreb123!\n";
