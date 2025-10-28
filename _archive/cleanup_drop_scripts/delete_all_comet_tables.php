<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== COMET TABELLEN IN TENANT-DATENBANKEN ===\n\n";

try {
    // Hole alle Tenants aus der Central DB
    $tenants = DB::connection('central')
        ->table('tenants')
        ->select('id', 'data')
        ->get();

    echo "Gefundene Tenants: " . $tenants->count() . "\n\n";

    $allTablesToDelete = [];

    foreach ($tenants as $tenant) {
        $tenantData = json_decode($tenant->data, true);
        $tenantId = $tenant->id;
        $dbName = $tenantData['tenancy_db_name'] ?? null;

        if (!$dbName) {
            echo "⚠️  Tenant {$tenantId}: Keine DB gefunden, überspringe...\n\n";
            continue;
        }

        echo str_repeat('=', 80) . "\n";
        echo "📦 Tenant: {$tenantId} (DB: {$dbName})\n";
        echo str_repeat('=', 80) . "\n";

        // Tenant-Datenbank Verbindung konfigurieren
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Hole alle comet_* Tabellen
        $tables = DB::connection('tenant')
            ->select("SHOW TABLES LIKE 'comet_%'");

        if (empty($tables)) {
            echo "   ℹ️  Keine comet_* Tabellen vorhanden\n\n";
            continue;
        }

        $tableNames = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables);

        sort($tableNames);

        echo "   Gefundene comet_* Tabellen: " . count($tableNames) . "\n\n";

        foreach ($tableNames as $table) {
            // Zähle Einträge
            $count = DB::connection('tenant')
                ->table($table)
                ->count();

            echo "   📋 {$table}";
            if ($count > 0) {
                echo " ({$count} Einträge)";
            } else {
                echo " (leer)";
            }
            echo "\n";

            // Zur Löschliste hinzufügen
            $allTablesToDelete[] = [
                'tenant' => $tenantId,
                'db' => $dbName,
                'table' => $table,
                'count' => $count
            ];
        }

        echo "\n";
    }

    // Zusammenfassung
    echo str_repeat('=', 80) . "\n";
    echo "ZUSAMMENFASSUNG\n";
    echo str_repeat('=', 80) . "\n\n";

    echo "Insgesamt werden " . count($allTablesToDelete) . " Tabellen gelöscht:\n\n";

    // Gruppiere nach Tabellennamen
    $tablesByName = [];
    $totalRows = 0;

    foreach ($allTablesToDelete as $item) {
        $tableName = $item['table'];
        if (!isset($tablesByName[$tableName])) {
            $tablesByName[$tableName] = [
                'count' => 0,
                'rows' => 0,
                'tenants' => []
            ];
        }
        $tablesByName[$tableName]['count']++;
        $tablesByName[$tableName]['rows'] += $item['count'];
        $tablesByName[$tableName]['tenants'][] = $item['tenant'];
        $totalRows += $item['count'];
    }

    ksort($tablesByName);

    foreach ($tablesByName as $tableName => $info) {
        echo "   • {$tableName}";
        echo " - in {$info['count']} Tenant(s)";
        if ($info['rows'] > 0) {
            echo " - {$info['rows']} Einträge gesamt";
        }
        echo "\n";
        echo "     Tenants: " . implode(', ', $info['tenants']) . "\n";
    }

    echo "\n";
    echo "📊 GESAMT: " . count($allTablesToDelete) . " Tabellen, {$totalRows} Einträge\n\n";

    // Bestätigung einholen
    echo str_repeat('=', 80) . "\n";
    echo "⚠️  WARNUNG: Diese Aktion kann NICHT rückgängig gemacht werden!\n";
    echo str_repeat('=', 80) . "\n\n";

    echo "Möchten Sie fortfahren und ALLE diese Tabellen löschen? (ja/nein): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $answer = trim(strtolower($line));
    fclose($handle);

    if ($answer !== 'ja' && $answer !== 'yes' && $answer !== 'y') {
        echo "\n❌ Abgebrochen. Keine Tabellen wurden gelöscht.\n";
        exit(0);
    }

    // Lösche die Tabellen
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "🗑️  LÖSCHE TABELLEN...\n";
    echo str_repeat('=', 80) . "\n\n";

    $deleted = 0;
    $errors = [];

    foreach ($allTablesToDelete as $item) {
        $tenantId = $item['tenant'];
        $dbName = $item['db'];
        $table = $item['table'];

        try {
            // Verbindung konfigurieren
            config(['database.connections.tenant.database' => $dbName]);
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Tabelle löschen
            DB::connection('tenant')->statement("DROP TABLE IF EXISTS `{$table}`");

            echo "   ✅ {$tenantId}.{$table}\n";
            $deleted++;

        } catch (\Exception $e) {
            echo "   ❌ {$tenantId}.{$table} - FEHLER: " . $e->getMessage() . "\n";
            $errors[] = [
                'tenant' => $tenantId,
                'table' => $table,
                'error' => $e->getMessage()
            ];
        }
    }

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "ERGEBNIS\n";
    echo str_repeat('=', 80) . "\n\n";

    echo "✅ Erfolgreich gelöscht: {$deleted}/" . count($allTablesToDelete) . " Tabellen\n";

    if (!empty($errors)) {
        echo "❌ Fehler: " . count($errors) . "\n\n";
        echo "Details:\n";
        foreach ($errors as $error) {
            echo "   • {$error['tenant']}.{$error['table']}: {$error['error']}\n";
        }
    } else {
        echo "🎉 Alle Tabellen erfolgreich gelöscht!\n";
    }

} catch (\Exception $e) {
    echo "❌ FEHLER: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
