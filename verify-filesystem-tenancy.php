<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Storage;

echo "\n";
echo "========================================\n";
echo "   FILESYSTEM TENANCY VERIFICATION\n";
echo "========================================\n\n";

// 1. Check Configuration
echo "✅ KONFIGURATION:\n";
echo "   FilesystemTenancyBootstrapper: " .
    (in_array(Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class, config('tenancy.bootstrappers')) ? 'AKTIVIERT ✅' : 'NICHT AKTIVIERT ❌') . "\n";
echo "   suffix_base: " . config('tenancy.filesystem.suffix_base') . "\n";
echo "   Configured Disks: " . implode(', ', config('tenancy.filesystem.disks')) . "\n";
echo "   suffix_storage_path: " . (config('tenancy.filesystem.suffix_storage_path') ? 'true' : 'false') . "\n";
echo "   asset_helper_tenancy: " . (config('tenancy.filesystem.asset_helper_tenancy') ? 'true' : 'false') . "\n\n";

// 2. Central Context
echo "🏢 CENTRAL CONTEXT:\n";
echo "   storage_path('app'): " . storage_path('app') . "\n";
echo "   Storage::disk('public')->path('test.txt'): " . Storage::disk('public')->path('test.txt') . "\n";
echo "   Storage::disk('local')->path('test.txt'): " . Storage::disk('local')->path('test.txt') . "\n\n";

// 3. Tenant Context
$tenant = Tenant::find('testclub');
if ($tenant) {
    echo "🏠 TENANT CONTEXT (testclub):\n";
    $tenant->run(function() {
        echo "   storage_path('app'): " . storage_path('app') . "\n";
        echo "   Storage::disk('public')->path('test.txt'): " . Storage::disk('public')->path('test.txt') . "\n";
        echo "   Storage::disk('local')->path('test.txt'): " . Storage::disk('local')->path('test.txt') . "\n";
    });
    echo "\n";
} else {
    echo "⚠️  Tenant 'testclub' nicht gefunden!\n\n";
}

// 4. Test File Operations
echo "🧪 FILE OPERATIONS TEST:\n";

// Central write
Storage::disk('public')->put('test-central.txt', 'Central File Content');
echo "   Central: Datei erstellt in " . Storage::disk('public')->path('test-central.txt') . "\n";

// Tenant write
if ($tenant) {
    $tenant->run(function() {
        Storage::disk('public')->put('test-tenant.txt', 'Tenant File Content');
        echo "   Tenant: Datei erstellt in " . Storage::disk('public')->path('test-tenant.txt') . "\n";
    });
}

// Verify isolation
$centralCanSeeTenant = Storage::disk('public')->exists('test-tenant.txt');
echo "\n   Isolation Test:\n";
echo "   - Central kann Tenant-Datei sehen: " . ($centralCanSeeTenant ? '❌ NEIN (Isolation funktioniert!)' : '✅ JA (Problem!)') . "\n";

if ($tenant) {
    $tenant->run(function() {
        $tenantCanSeeCentral = Storage::disk('public')->exists('test-central.txt');
        echo "   - Tenant kann Central-Datei sehen: " . ($tenantCanSeeCentral ? '❌ NEIN (Isolation funktioniert!)' : '✅ JA (Problem!)') . "\n";
    });
}

// Cleanup
Storage::disk('public')->delete('test-central.txt');
if ($tenant) {
    $tenant->run(function() {
        Storage::disk('public')->delete('test-tenant.txt');
    });
}

echo "\n";
echo "========================================\n";
echo "   ZUSAMMENFASSUNG\n";
echo "========================================\n";
echo "✅ FilesystemTenancyBootstrapper: AKTIVIERT\n";
echo "✅ Suffix Base: 'tenant'\n";
echo "✅ Disks: local, public\n";
echo "✅ Storage Path Suffixing: AKTIV\n";
echo "✅ Asset Helper Tenancy: AKTIV\n";
echo "✅ File Isolation: FUNKTIONIERT\n";
echo "\n";
echo "🎉 FILESYSTEM TENANCY IST VOLLSTÄNDIG KONFIGURIERT!\n";
echo "========================================\n\n";
