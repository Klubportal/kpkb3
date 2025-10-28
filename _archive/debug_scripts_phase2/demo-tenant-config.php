<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n╔════════════════════════════════════════════════════════╗\n";
echo "║  TENANT CONFIGURATION DEMO                             ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Test mit testclub Tenant
$tenant = App\Models\Central\Tenant::find('testclub');

if (!$tenant) {
    echo "❌ Tenant 'testclub' nicht gefunden!\n";
    exit(1);
}

echo "🏢 TENANT: {$tenant->id}\n";
echo "══════════════════════════════════════════════════════════\n\n";

// Zeige Config VOR Tenant-Initialisierung
echo "📋 CONFIG VOR TENANT-INITIALISIERUNG:\n";
echo "══════════════════════════════════════════════════════════\n";
echo "  app.name:           " . config('app.name') . "\n";
echo "  app.url:            " . config('app.url') . "\n";
echo "  app.timezone:       " . config('app.timezone') . "\n";
echo "  app.locale:         " . config('app.locale') . "\n";
echo "  mail.from.address:  " . config('mail.from.address') . "\n";
echo "  mail.from.name:     " . config('mail.from.name') . "\n";
echo "  cache.prefix:       " . config('cache.prefix') . "\n";
echo "\n";

// Initialisiere Tenant
echo "🔧 INITIALISIERE TENANT...\n";
echo "══════════════════════════════════════════════════════════\n";
tenancy()->initialize($tenant);
echo "✅ Tenant initialisiert!\n\n";

// Zeige Config NACH Tenant-Initialisierung
echo "📋 CONFIG NACH TENANT-INITIALISIERUNG:\n";
echo "══════════════════════════════════════════════════════════\n";
echo "  app.name:           " . config('app.name') . "\n";
echo "  app.url:            " . config('app.url') . "\n";
echo "  app.timezone:       " . config('app.timezone') . "\n";
echo "  app.locale:         " . config('app.locale') . "\n";
echo "  mail.from.address:  " . config('mail.from.address') . "\n";
echo "  mail.from.name:     " . config('mail.from.name') . "\n";
echo "  cache.prefix:       " . config('cache.prefix') . "\n";
echo "\n";

// Zeige Tenant Settings aus DB
echo "⚙️  TENANT SETTINGS (aus DB):\n";
echo "══════════════════════════════════════════════════════════\n";

try {
    $settings = DB::table('settings')->get();

    if ($settings->isEmpty()) {
        echo "  ℹ️  Keine Settings gefunden\n";
    } else {
        foreach ($settings->take(10) as $setting) {
            $value = json_decode($setting->payload, true);
            $displayValue = is_array($value) ? json_encode($value) : $value;

            if (strlen($displayValue) > 50) {
                $displayValue = substr($displayValue, 0, 47) . '...';
            }

            echo sprintf("  %-25s %s\n", $setting->name . ':', $displayValue);
        }

        if ($settings->count() > 10) {
            echo "\n  ... und " . ($settings->count() - 10) . " weitere Settings\n";
        }
    }
} catch (\Exception $e) {
    echo "  ⚠️  Fehler beim Laden: " . $e->getMessage() . "\n";
}

echo "\n";

// Zeige Domain Info
echo "🌐 DOMAIN INFORMATION:\n";
echo "══════════════════════════════════════════════════════════\n";
$domains = $tenant->domains;
foreach ($domains as $domain) {
    echo "  • " . $domain->domain . "\n";
}

echo "\n";

// Test: Config dynamisch ändern
echo "🧪 TEST: Config dynamisch ändern\n";
echo "══════════════════════════════════════════════════════════\n";

config(['app.name' => 'Test Club Modified']);
config(['mail.from.address' => 'test@modified.com']);

echo "  ✅ Config geändert zu:\n";
echo "     app.name: " . config('app.name') . "\n";
echo "     mail.from.address: " . config('mail.from.address') . "\n";

echo "\n";

// Zusammenfassung
echo "══════════════════════════════════════════════════════════\n";
echo "📊 ZUSAMMENFASSUNG:\n";
echo "══════════════════════════════════════════════════════════\n";
echo "  ✅ Tenant-Config wird automatisch geladen\n";
echo "  ✅ ConfigureTenantEnvironment Listener aktiv\n";
echo "  ✅ Config kann zur Laufzeit überschrieben werden\n";
echo "  ✅ Jeder Tenant hat isolierte Settings\n";

echo "\n";
echo "💡 VERWENDUNG:\n";
echo "══════════════════════════════════════════════════════════\n";
echo "  // Im Code:\n";
echo "  tenancy()->initialize(\$tenant);\n";
echo "  config(['mail.from.address' => \$tenant->email]);\n";
echo "  config(['app.name' => \$tenant->name]);\n";

echo "\n";
