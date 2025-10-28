<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TENANT DATABASE CONNECTION TEST ===" . PHP_EOL . PHP_EOL;

// Find tenant
$tenant = App\Models\Central\Tenant::where('id', 'testclub')->first();

if (!$tenant) {
    echo "❌ Tenant 'testclub' NOT FOUND!" . PHP_EOL;
    exit(1);
}

echo "✅ Tenant Found: " . $tenant->id . PHP_EOL;
echo "   Domain: " . ($tenant->domains->first()->domain ?? 'N/A') . PHP_EOL . PHP_EOL;

// Initialize tenancy
tenancy()->initialize($tenant);

echo "=== AFTER TENANCY INITIALIZATION ===" . PHP_EOL;
echo "Current Tenant ID: " . tenant('id') . PHP_EOL;
echo "Tenant DB Name: " . config('database.connections.tenant.database') . PHP_EOL;
echo "Session Connection: " . config('session.connection') . PHP_EOL;
echo "Session Table: " . config('session.table') . PHP_EOL . PHP_EOL;

// Test tenant database connection
try {
    $usersCount = DB::connection('tenant')->table('users')->count();
    echo "✅ Tenant DB Connected!" . PHP_EOL;
    echo "   Users Count: " . $usersCount . PHP_EOL;

    $sessionsCount = DB::connection('tenant')->table('sessions')->count();
    echo "   Sessions Count: " . $sessionsCount . PHP_EOL . PHP_EOL;

    // Check specific user
    $user = DB::connection('tenant')->table('users')->where('email', 'admin@testclub.com')->first();
    if ($user) {
        echo "✅ User 'admin@testclub.com' EXISTS!" . PHP_EOL;
        echo "   ID: " . $user->id . PHP_EOL;
        echo "   Name: " . $user->name . PHP_EOL;
        echo "   Email Verified: " . ($user->email_verified_at ? 'YES' : 'NO') . PHP_EOL;

        // Test password
        if (Hash::check('Zagreb123!', $user->password)) {
            echo "   Password 'Zagreb123!': ✅ CORRECT" . PHP_EOL;
        } else {
            echo "   Password 'Zagreb123!': ❌ WRONG" . PHP_EOL;
        }
    } else {
        echo "❌ User 'admin@testclub.com' NOT FOUND in tenant DB!" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== SESSION CONFIGURATION ===" . PHP_EOL;
echo "Driver: " . config('session.driver') . PHP_EOL;
echo "Connection: " . config('session.connection') . PHP_EOL;
echo "Table: " . config('session.table') . PHP_EOL;
echo "Cookie: " . config('session.cookie') . PHP_EOL;
echo "Domain: " . (config('session.domain') ?: '(empty)') . PHP_EOL;
echo "Same Site: " . config('session.same_site') . PHP_EOL;
