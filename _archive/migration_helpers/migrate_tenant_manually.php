<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== TENANT MIGRATIONEN MANUELL AUSFÜHREN ===\n\n";

// Neue separate Verbindung zur tenant_testclub Datenbank
config(['database.connections.tenant_direct' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'tenant_testclub',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]]);

DB::setDefaultConnection('tenant_direct');

echo "Verbunden mit: " . DB::connection()->getDatabaseName() . "\n\n";

// Migrations-Tabelle erstellen
if (!Schema::hasTable('migrations')) {
    Schema::create('migrations', function (Blueprint $table) {
        $table->id();
        $table->string('migration');
        $table->integer('batch');
    });
    echo "✓ Migrations-Tabelle erstellt\n";
}

// Alle Tenant-Migrations aus dem tenant Ordner holen
$migrationFiles = glob(__DIR__ . '/database/migrations/tenant/*.php');
sort($migrationFiles);

$batch = 1;
$executed = 0;

foreach ($migrationFiles as $file) {
    $migrationName = basename($file, '.php');

    // Prüfen ob bereits ausgeführt
    $exists = DB::table('migrations')->where('migration', $migrationName)->exists();

    if ($exists) {
        echo "⏭ Übersprungen: $migrationName (bereits ausgeführt)\n";
        continue;
    }

    echo "⚙ Führe aus: $migrationName\n";

    try {
        // Migration laden (nur include, kein require_once wegen Klassen-Konflikten)
        $migration = include $file;

        if (is_object($migration)) {
            $migration->up();
        }

        // In migrations Tabelle eintragen
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $batch
        ]);

        echo "✓ Erfolgreich: $migrationName\n\n";
        $executed++;    } catch (Exception $e) {
        echo "✗ FEHLER bei $migrationName: " . $e->getMessage() . "\n\n";
        break;
    }
}

echo "\n=== FERTIG ===\n";
echo "Ausgeführt: $executed Migrationen\n";
echo "Datenbank: " . DB::connection()->getDatabaseName() . "\n";

// Tabellen anzeigen
echo "\n=== TABELLEN IN tenant_testclub ===\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "  - $tableName\n";
}
