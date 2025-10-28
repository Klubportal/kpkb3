<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "   TENANT MIGRATIONS AUSFÜHREN\n";
echo "========================================\n\n";

$tenantId = $argv[1] ?? 'testclub';

// Tenant finden
$tenant = Tenant::where('id', $tenantId)->first();

if (!$tenant) {
    echo "❌ Tenant '{$tenantId}' nicht gefunden!\n\n";
    exit(1);
}

echo "✅ Tenant: {$tenant->id}\n";
echo "   Database: tenant_{$tenant->id}\n\n";

// Tenancy initialisieren
tenancy()->initialize($tenant);

// Manuell DB Bootstrapper ausführen
$bootstrapper = app(\Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class);
$bootstrapper->bootstrap($tenant);

$currentDB = DB::connection()->getDatabaseName();
echo "🔄 Verbunden mit: {$currentDB}\n\n";

// Migration-Pfad
$path = 'database/migrations/tenant';

echo "📂 Migration-Pfad: {$path}\n\n";

// Migrations ausführen
echo "⚙️  Führe Migrations aus...\n\n";

try {
    Artisan::call('migrate', [
        '--path' => $path,
        '--force' => true,
    ]);

    echo Artisan::output();
    echo "\n✅ Migrations erfolgreich ausgeführt!\n\n";

} catch (\Exception $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n\n";
}

// Prüfe welche Tabellen jetzt existieren
echo "========================================\n";
echo "   TABELLEN IN TENANT DB\n";
echo "========================================\n\n";

$tables = DB::select('SHOW TABLES');
$tableKey = "Tables_in_tenant_{$tenant->id}";

foreach ($tables as $table) {
    echo "  - " . $table->$tableKey . "\n";
}

echo "\n========================================\n\n";
