#!/usr/bin/env php
<?php

/**
 * Tenant-DB Migrationen manuell ausführen
 *
 * Dieses Script führt die Migrationen direkt in tenant_testclub aus
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n========================================\n";
echo "   TENANT DB SETUP\n";
echo "========================================\n\n";

// Temporäre Connection zur tenant_testclub DB erstellen
config(['database.connections.temp_tenant' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'tenant_testclub',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]]);

// Connection wechseln
DB::purge('temp_tenant');
DB::setDefaultConnection('temp_tenant');

echo "✅ Verbindung zu tenant_testclub hergestellt\n\n";

// Prüfen ob Tabellen existieren
$tables = DB::select('SHOW TABLES');
echo "Aktuelle Tabellen: " . count($tables) . "\n";

if (count($tables) > 0) {
    echo "\nExistierende Tabellen:\n";
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "  - $tableName\n";
    }
} else {
    echo "  (keine Tabellen vorhanden)\n";
}

echo "\n========================================\n";
echo "   MIGRATIONEN AUSFÜHREN\n";
echo "========================================\n\n";

// Migrationen ausführen
try {
    Artisan::call('migrate', [
        '--database' => 'temp_tenant',
        '--path' => 'database/migrations',
        '--force' => true,
    ]);

    echo Artisan::output();

    echo "\n✅ Standard-Migrationen ausgeführt!\n\n";
} catch (\Exception $e) {
    echo "❌ Fehler bei Standard-Migrationen: " . $e->getMessage() . "\n\n";
}

// Tenant-spezifische Migrationen
try {
    Artisan::call('migrate', [
        '--database' => 'temp_tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);

    echo Artisan::output();

    echo "\n✅ Tenant-Migrationen ausgeführt!\n\n";
} catch (\Exception $e) {
    echo "❌ Fehler bei Tenant-Migrationen: " . $e->getMessage() . "\n\n";
}

// Final check
$tablesAfter = DB::select('SHOW TABLES');
echo "========================================\n";
echo "   ERGEBNIS\n";
echo "========================================\n\n";
echo "Tabellen in tenant_testclub: " . count($tablesAfter) . "\n\n";

if (count($tablesAfter) > 0) {
    echo "✅ Datenbank erfolgreich eingerichtet!\n\n";

    // User-Tabelle prüfen
    if (Schema::hasTable('users')) {
        $userCount = DB::table('users')->count();
        echo "Users in DB: $userCount\n";

        if ($userCount == 0) {
            echo "\n⚠️  Keine Users vorhanden.\n";
            echo "Führe aus: php create_tenant_user.php\n";
        }
    }
} else {
    echo "❌ Keine Tabellen erstellt!\n";
    echo "Prüfe die Fehler oben.\n";
}

echo "\n========================================\n";
