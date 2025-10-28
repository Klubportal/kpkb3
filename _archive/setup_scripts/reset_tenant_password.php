<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Central\Tenant::where('id', 'testclub')->first();

if (!$tenant) {
    echo "❌ Tenant not found!" . PHP_EOL;
    exit(1);
}

tenancy()->initialize($tenant);

$user = DB::connection('tenant')->table('users')->where('email', 'admin@testclub.com')->first();

if (!$user) {
    echo "❌ User not found!" . PHP_EOL;
    exit(1);
}

$newPassword = Hash::make('Zagreb123!');

DB::connection('tenant')->table('users')
    ->where('id', $user->id)
    ->update(['password' => $newPassword]);

echo "✅ Password für 'admin@testclub.com' auf 'Zagreb123!' gesetzt!" . PHP_EOL;

// Verify
$updatedUser = DB::connection('tenant')->table('users')->where('id', $user->id)->first();
if (Hash::check('Zagreb123!', $updatedUser->password)) {
    echo "✅ Password verifiziert - Login sollte jetzt funktionieren!" . PHP_EOL;
} else {
    echo "❌ Verifikation fehlgeschlagen!" . PHP_EOL;
}
