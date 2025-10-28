<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n╔════════════════════════════════════════════════════════╗\n";
echo "║  TENANT DATENBANKEN ERSTELLEN                          ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Get all tenants
$tenants = App\Models\Central\Tenant::all();

echo "📊 Gefundene Tenants: " . $tenants->count() . "\n";
echo "══════════════════════════════════════════════════════════\n\n";

$created = 0;
$existing = 0;
$errors = 0;

foreach ($tenants as $tenant) {
    $dbName = $tenant->database()->getName();

    echo "🔧 Tenant: {$tenant->id}\n";
    echo "   DB: {$dbName}\n";

    try {
        // Check if database exists
        $exists = DB::connection('central')
            ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);

        if (empty($exists)) {
            // Create database
            DB::connection('central')->statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "   ✅ Datenbank erstellt!\n";
            $created++;
        } else {
            echo "   ℹ️  Datenbank existiert bereits\n";
            $existing++;
        }
    } catch (\Exception $e) {
        echo "   ❌ Fehler: " . $e->getMessage() . "\n";
        $errors++;
    }

    echo "\n";
}

echo "══════════════════════════════════════════════════════════\n";
echo "📊 ZUSAMMENFASSUNG:\n";
echo "══════════════════════════════════════════════════════════\n";
echo "  ✅ Neu erstellt: $created\n";
echo "  ℹ️  Bereits vorhanden: $existing\n";
echo "  ❌ Fehler: $errors\n";
echo "══════════════════════════════════════════════════════════\n\n";

if ($created > 0) {
    echo "🚀 NÄCHSTER SCHRITT:\n";
    echo "══════════════════════════════════════════════════════════\n";
    echo "Die Datenbanken wurden erstellt. Jetzt Migrations ausführen:\n\n";
    echo "  php artisan tenants:migrate\n\n";
} elseif ($errors == 0) {
    echo "🚀 NÄCHSTER SCHRITT:\n";
    echo "══════════════════════════════════════════════════════════\n";
    echo "Alle Datenbanken existieren bereits. Falls Migrations fehlen:\n\n";
    echo "  php artisan tenants:migrate\n\n";
}

echo "\n";
