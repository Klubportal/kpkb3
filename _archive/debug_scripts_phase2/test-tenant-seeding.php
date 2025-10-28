<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n╔════════════════════════════════════════════════════════╗\n";
echo "║  TENANT SEEDING TEST                                   ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Get all tenants
$tenants = DB::connection('central')->table('tenants')->get(['id', 'data']);

echo "📊 GEFUNDENE TENANTS:\n";
echo "══════════════════════════════════════════════════════════\n";

foreach ($tenants as $tenant) {
    $data = json_decode($tenant->data, true);
    $dbName = $data['tenancy_db_name'] ?? 'unknown';

    echo sprintf("  ID: %-15s DB: %s\n", $tenant->id, $dbName);
}

echo "\n";
echo "══════════════════════════════════════════════════════════\n";
echo "Anzahl Tenants: " . $tenants->count() . "\n";
echo "══════════════════════════════════════════════════════════\n\n";

// Check if tenant databases exist
echo "🔍 DATENBANK-CHECK:\n";
echo "══════════════════════════════════════════════════════════\n";

foreach ($tenants as $tenant) {
    $data = json_decode($tenant->data, true);
    $dbName = $data['tenancy_db_name'] ?? null;

    if (!$dbName) {
        echo "  ❌ {$tenant->id}: Kein DB Name gefunden\n";
        continue;
    }

    try {
        $exists = DB::connection('central')
            ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);

        if (!empty($exists)) {
            echo "  ✅ {$tenant->id}: Datenbank '{$dbName}' existiert\n";

            // Check if tables exist
            $tables = DB::connection('central')
                ->select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?", [$dbName]);

            echo "     └─ Tabellen: " . $tables[0]->count . "\n";
        } else {
            echo "  ❌ {$tenant->id}: Datenbank '{$dbName}' FEHLT!\n";
        }
    } catch (\Exception $e) {
        echo "  ⚠️  {$tenant->id}: Fehler beim Prüfen: " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo "══════════════════════════════════════════════════════════\n";
echo "💡 NÄCHSTE SCHRITTE:\n";
echo "══════════════════════════════════════════════════════════\n\n";

$missingDbs = [];
foreach ($tenants as $tenant) {
    $data = json_decode($tenant->data, true);
    $dbName = $data['tenancy_db_name'] ?? null;

    if ($dbName) {
        $exists = DB::connection('central')
            ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);

        if (empty($exists)) {
            $missingDbs[] = $tenant->id;
        }
    }
}

if (!empty($missingDbs)) {
    echo "❌ Folgende Tenants haben keine Datenbank:\n";
    foreach ($missingDbs as $id) {
        echo "   - $id\n";
    }
    echo "\n";
    echo "✅ Führe aus:\n";
    echo "   php artisan tenants:migrate\n";
    echo "\n";
    echo "   Das erstellt die fehlenden Datenbanken!\n";
} else {
    echo "✅ Alle Tenant-Datenbanken existieren!\n\n";
    echo "🌱 Jetzt kannst du seeden:\n";
    echo "   php artisan tenants:seed --class=Database\\Seeders\\Tenant\\TenantDatabaseSeeder\n\n";
    echo "   Oder nur einen Tenant:\n";
    echo "   php artisan tenants:seed --tenants=testclub --class=Database\\Seeders\\Tenant\\TenantDatabaseSeeder\n";
}

echo "\n";
