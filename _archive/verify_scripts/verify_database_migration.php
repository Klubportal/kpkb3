<?php

echo "=========================================\n";
echo "  DATENBANK VERGLEICH: klubportal_landlord vs kpkb3\n";
echo "=========================================\n\n";

$pdo = new PDO('mysql:host=127.0.0.1', 'root', '');

// Prüfe ob beide Datenbanken existieren
$dbs = $pdo->query("SHOW DATABASES LIKE 'klubportal_landlord'")->fetchAll();
$hasOldDb = count($dbs) > 0;

$dbs = $pdo->query("SHOW DATABASES LIKE 'kpkb3'")->fetchAll();
$hasNewDb = count($dbs) > 0;

echo "Datenbank Status:\n";
echo "-----------------\n";
echo "klubportal_landlord: " . ($hasOldDb ? "✓ Existiert" : "✗ Nicht gefunden") . "\n";
echo "kpkb3: " . ($hasNewDb ? "✓ Existiert" : "✗ Nicht gefunden") . "\n\n";

if (!$hasNewDb) {
    die("FEHLER: kpkb3 Datenbank nicht gefunden!\n");
}

// Vergleiche Tabellen
if ($hasOldDb) {
    echo "Tabellen-Vergleich:\n";
    echo "===================\n\n";

    $oldTables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'klubportal_landlord' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
    $newTables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kpkb3' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);

    echo "Tabellen in klubportal_landlord: " . count($oldTables) . "\n";
    echo "Tabellen in kpkb3: " . count($newTables) . "\n\n";

    $missing = array_diff($oldTables, $newTables);
    if (count($missing) > 0) {
        echo "⚠ Fehlende Tabellen in kpkb3:\n";
        foreach ($missing as $table) {
            echo "  - $table\n";
        }
        echo "\n";
    } else {
        echo "✓ Alle Tabellen wurden kopiert\n\n";
    }

    // Vergleiche Datensätze für wichtige Tabellen
    echo "Datensatz-Vergleich:\n";
    echo "====================\n\n";

    $importantTables = ['tenants', 'users', 'domains', 'settings', 'comet_competitions', 'comet_matches'];

    foreach ($importantTables as $table) {
        if (in_array($table, $oldTables) && in_array($table, $newTables)) {
            $oldCount = $pdo->query("SELECT COUNT(*) FROM klubportal_landlord.$table")->fetchColumn();
            $newCount = $pdo->query("SELECT COUNT(*) FROM kpkb3.$table")->fetchColumn();

            $status = $oldCount == $newCount ? "✓" : "⚠";
            echo "$status $table: klubportal_landlord=$oldCount, kpkb3=$newCount\n";
        }
    }
    echo "\n";
}

// Zeige aktuelle kpkb3 Statistiken
echo "kpkb3 Datenbank Übersicht:\n";
echo "==========================\n\n";

$stats = $pdo->query("
    SELECT
        COUNT(*) as table_count,
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
        ROUND(SUM(data_length) / 1024 / 1024, 2) AS data_mb,
        ROUND(SUM(index_length) / 1024 / 1024, 2) AS index_mb
    FROM information_schema.tables
    WHERE table_schema = 'kpkb3'
")->fetch(PDO::FETCH_ASSOC);

echo "Anzahl Tabellen: {$stats['table_count']}\n";
echo "Gesamtgröße: {$stats['size_mb']} MB\n";
echo "Daten: {$stats['data_mb']} MB\n";
echo "Indizes: {$stats['index_mb']} MB\n\n";

// Wichtige Daten
echo "Wichtige Datensätze in kpkb3:\n";
echo "=============================\n\n";

$tenants = $pdo->query("SELECT COUNT(*) FROM kpkb3.tenants")->fetchColumn();
$users = $pdo->query("SELECT COUNT(*) FROM kpkb3.users")->fetchColumn();
$domains = $pdo->query("SELECT COUNT(*) FROM kpkb3.domains")->fetchColumn();

echo "Tenants: $tenants\n";
echo "Users: $users\n";
echo "Domains: $domains\n\n";

// Liste Tenants
echo "Registrierte Tenants:\n";
echo "---------------------\n";
$tenantList = $pdo->query("SELECT id, name FROM kpkb3.tenants ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tenantList as $tenant) {
    echo "- {$tenant['id']}: {$tenant['name']}\n";
}
echo "\n";

// Prüfe Tenant-Datenbanken
echo "Tenant-Datenbanken:\n";
echo "===================\n\n";
foreach ($tenantList as $tenant) {
    $dbName = "tenant_{$tenant['id']}";
    $exists = $pdo->query("SHOW DATABASES LIKE '$dbName'")->fetchAll();

    if (count($exists) > 0) {
        $tableCount = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$dbName'")->fetchColumn();
        echo "✓ $dbName ($tableCount Tabellen)\n";
    } else {
        echo "✗ $dbName (nicht gefunden)\n";
    }
}

echo "\n✓ Datenbank-Überprüfung abgeschlossen!\n";
