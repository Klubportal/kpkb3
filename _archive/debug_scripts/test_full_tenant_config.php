<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TENANT PANEL CONFIGURATION CHECK ===" . PHP_EOL . PHP_EOL;

// Get Filament panels
$panels = \Filament\Facades\Filament::getPanels();

foreach ($panels as $panel) {
    echo "Panel ID: " . $panel->getId() . PHP_EOL;
    echo "Panel Path: " . $panel->getPath() . PHP_EOL;
    echo "Auth Guard: " . $panel->getAuthGuard() . PHP_EOL;
    echo "Auth Password Broker: " . ($panel->getAuthPasswordBroker() ?? 'default') . PHP_EOL;

    // Check guard configuration
    $guardName = $panel->getAuthGuard();
    $guardConfig = config("auth.guards.{$guardName}");

    if ($guardConfig) {
        echo "Guard Driver: " . $guardConfig['driver'] . PHP_EOL;
        echo "Guard Provider: " . $guardConfig['provider'] . PHP_EOL;

        $provider = $guardConfig['provider'];
        $providerConfig = config("auth.providers.{$provider}");

        if ($providerConfig) {
            echo "Provider Driver: " . $providerConfig['driver'] . PHP_EOL;
            echo "Provider Model: " . $providerConfig['model'] . PHP_EOL;

            // Check model connection
            $modelClass = $providerConfig['model'];
            if (class_exists($modelClass)) {
                $model = new $modelClass;
                $connection = $model->getConnectionName();
                echo "Model Connection: " . ($connection ?: 'default') . PHP_EOL;
                echo "Model Table: " . $model->getTable() . PHP_EOL;
            }
        }
    }

    echo PHP_EOL . "---" . PHP_EOL . PHP_EOL;
}

echo "=== AUTH CONFIGURATION ===" . PHP_EOL;
echo "Default Guard: " . config('auth.defaults.guard') . PHP_EOL;
echo "Default Password Broker: " . config('auth.defaults.passwords') . PHP_EOL;
echo PHP_EOL;

// Test tenant resolution
echo "=== TENANT RESOLUTION TEST ===" . PHP_EOL;
$domain = 'testclub.localhost';
$tenantDomain = DB::connection('central')->table('domains')->where('domain', $domain)->first();

if ($tenantDomain) {
    echo "✅ Domain '$domain' resolves to tenant: " . $tenantDomain->tenant_id . PHP_EOL;

    $tenant = App\Models\Central\Tenant::find($tenantDomain->tenant_id);
    if ($tenant) {
        tenancy()->initialize($tenant);
        echo "✅ Tenancy initialized for: " . tenant('id') . PHP_EOL;
        echo "Tenant DB: " . config('database.connections.tenant.database') . PHP_EOL;

        // Try to load user with Eloquent
        $user = App\Models\Tenant\User::where('email', 'admin@testclub.com')->first();

        if ($user) {
            echo "✅ User loaded via Eloquent!" . PHP_EOL;
            echo "   Connection: " . $user->getConnectionName() . PHP_EOL;
            echo "   ID: " . $user->id . PHP_EOL;
            echo "   Email: " . $user->email . PHP_EOL;

            // Test authentication
            $credentials = [
                'email' => 'admin@testclub.com',
                'password' => 'Zagreb123!'
            ];

            echo PHP_EOL . "=== TESTING AUTHENTICATION ===" . PHP_EOL;

            // Test with tenant guard
            if (Auth::guard('tenant')->validate($credentials)) {
                echo "✅ Auth::guard('tenant')->validate() - SUCCESS!" . PHP_EOL;
            } else {
                echo "❌ Auth::guard('tenant')->validate() - FAILED!" . PHP_EOL;
            }

            // Test manual password check
            if (Hash::check('Zagreb123!', $user->password)) {
                echo "✅ Manual password check - SUCCESS!" . PHP_EOL;
            } else {
                echo "❌ Manual password check - FAILED!" . PHP_EOL;
            }

        } else {
            echo "❌ User NOT found via Eloquent!" . PHP_EOL;
        }
    }
} else {
    echo "❌ Domain '$domain' NOT FOUND!" . PHP_EOL;
}

echo PHP_EOL . "=== LIVEWIRE CONFIGURATION ===" . PHP_EOL;
echo "Livewire Update Path: /livewire/update" . PHP_EOL;
echo "CSRF Exception: " . (in_array('livewire/*', app(\App\Http\Middleware\VerifyCsrfToken::class)->except ?? []) ? 'YES' : 'NO') . PHP_EOL;
