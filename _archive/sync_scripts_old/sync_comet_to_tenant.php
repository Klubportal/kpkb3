<?php

/**
 * Synchronisiert alle COMET-Daten von kpkb3 zu tenant_nkprigorjem
 */

echo "=== COMET Daten Synchronisation ===\n\n";

// Verbindung zu beiden Datenbanken
$landlord = new PDO('mysql:host=localhost;dbname=kpkb3;charset=utf8mb4', 'root', '');
$tenant = new PDO('mysql:host=localhost;dbname=tenant_nkprigorjem;charset=utf8mb4', 'root', '');

$landlord->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$tenant->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Alle COMET Tabellen finden
$tables = $landlord->query('SHOW TABLES LIKE "comet_%"')->fetchAll(PDO::FETCH_COLUMN);

echo "Gefundene COMET Tabellen: " . count($tables) . "\n\n";

foreach ($tables as $table) {
    echo "Synchronisiere $table...\n";

    try {
        // Zähle Einträge in Landlord
        $landlordCount = $landlord->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  - Landlord: $landlordCount Einträge\n";

        // Prüfe ob Tabelle in Tenant existiert
        $tenantTableExists = $tenant->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;

        if (!$tenantTableExists) {
            echo "  ⚠️  Tabelle existiert nicht in Tenant - überspringe\n\n";
            continue;
        }

        // Zähle Einträge in Tenant (vor Sync)
        $tenantCountBefore = $tenant->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  - Tenant (vorher): $tenantCountBefore Einträge\n";

        // Leere Tenant-Tabelle
        $tenant->exec("TRUNCATE TABLE $table");

        // Hole alle Daten aus Landlord
        $data = $landlord->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($data)) {
            echo "  ✓ Keine Daten zu kopieren\n\n";
            continue;
        }

        // Kopiere Daten zu Tenant
        $columns = array_keys($data[0]);
        $placeholders = ':' . implode(', :', $columns);
        $columnList = '`' . implode('`, `', $columns) . '`';

        $insertSql = "INSERT INTO $table ($columnList) VALUES ($placeholders)";
        $stmt = $tenant->prepare($insertSql);

        $insertedCount = 0;
        foreach ($data as $row) {
            $stmt->execute($row);
            $insertedCount++;
        }

        echo "  ✓ $insertedCount Einträge kopiert\n\n";

    } catch (Exception $e) {
        echo "  ❌ Fehler: " . $e->getMessage() . "\n\n";
    }
}

// Zusammenfassung
echo "\n=== Zusammenfassung ===\n";
foreach ($tables as $table) {
    try {
        $tenantTableExists = $tenant->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        if (!$tenantTableExists) continue;

        $count = $tenant->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "$table: $count Einträge\n";
    } catch (Exception $e) {
        echo "$table: Fehler\n";
    }
}

echo "\n✓ Synchronisation abgeschlossen!\n";
