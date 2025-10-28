<?php

echo "=== DEMO-DATEN IN TENANT-DATENBANKEN ===\n\n";

// PDO-Verbindung zur MySQL
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("❌ Verbindung zu MySQL fehlgeschlagen: " . $e->getMessage() . "\n");
}

// Hole alle Tenant-Datenbanken
$stmt = $pdo->query("SHOW DATABASES LIKE 'tenant_%'");
$databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($databases)) {
    echo "✅ Keine Tenant-Datenbanken gefunden.\n";
    exit(0);
}

// Erstelle Tenants-Array
$tenants = array_map(function($dbName) {
    return [
        'id' => str_replace('tenant_', '', $dbName),
        'db' => $dbName
    ];
}, $databases);

echo "Gefundene Tenants: " . count($tenants) . "\n\n";

// Tabellen die Demo-Daten enthalten
$demoTables = [
    'users' => 'Demo-User (aus DemoUserSeeder)',
    'events' => 'Events (aus EventSeeder)',
    'matches' => 'Spiele (aus MatchSeeder)',
    'players' => 'Spieler (aus PlayerSeeder)',
    'teams' => 'Teams (aus TeamSeeder)',
    'news' => 'News (aus TenantNewsSeeder)',
    'news_categories' => 'News-Kategorien',
];

$allDataToDelete = [];

foreach ($tenants as $tenant) {
    $tenantId = $tenant['id'];
    $dbName = $tenant['db'];

    echo str_repeat('=', 80) . "\n";
    echo "📦 Tenant: {$tenantId} (DB: {$dbName})\n";
    echo str_repeat('=', 80) . "\n";

    // Verbindung zur Tenant-DB
    try {
        $tenantPdo = new PDO(
            "mysql:host=127.0.0.1;dbname={$dbName};charset=utf8mb4",
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        echo "   ❌ Verbindung fehlgeschlagen: " . $e->getMessage() . "\n\n";
        continue;
    }

    $tenantHasData = false;

    foreach ($demoTables as $table => $description) {
        try {
            // Prüfe ob Tabelle existiert
            $stmt = $tenantPdo->query("SHOW TABLES LIKE '{$table}'");
            $exists = $stmt->fetch();

            if (!$exists) {
                echo "   ⏭️  {$table} - Tabelle existiert nicht\n";
                continue;
            }

            // Zähle Einträge
            $stmt = $tenantPdo->query("SELECT COUNT(*) as count FROM `{$table}`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];

            if ($count > 0) {
                echo "   📋 {$table}: {$count} Einträge ({$description})\n";
                $allDataToDelete[] = [
                    'tenant' => $tenantId,
                    'db' => $dbName,
                    'table' => $table,
                    'count' => $count,
                    'description' => $description
                ];
                $tenantHasData = true;
            } else {
                echo "   ✓ {$table}: leer\n";
            }

        } catch (PDOException $e) {
            echo "   ⚠️  {$table}: Fehler - " . $e->getMessage() . "\n";
        }
    }

    if (!$tenantHasData) {
        echo "   ℹ️  Keine Demo-Daten vorhanden\n";
    }

    echo "\n";
}

// Zusammenfassung
echo str_repeat('=', 80) . "\n";
echo "ZUSAMMENFASSUNG\n";
echo str_repeat('=', 80) . "\n\n";

if (empty($allDataToDelete)) {
    echo "✅ Keine Demo-Daten gefunden. Alle Tenant-DBs sind bereits sauber!\n";
    exit(0);
}

echo "Insgesamt werden Daten aus " . count($allDataToDelete) . " Tabellen gelöscht:\n\n";

// Gruppiere nach Tabellennamen
$dataByTable = [];
$totalRows = 0;

foreach ($allDataToDelete as $item) {
    $tableName = $item['table'];
    if (!isset($dataByTable[$tableName])) {
        $dataByTable[$tableName] = [
            'count' => 0,
            'rows' => 0,
            'tenants' => [],
            'description' => $item['description']
        ];
    }
    $dataByTable[$tableName]['count']++;
    $dataByTable[$tableName]['rows'] += $item['count'];
    $dataByTable[$tableName]['tenants'][] = $item['tenant'];
    $totalRows += $item['count'];
}

foreach ($dataByTable as $tableName => $info) {
    echo "   • {$tableName} - {$info['description']}\n";
    echo "     Einträge: {$info['rows']} in {$info['count']} Tenant(s)\n";
    echo "     Tenants: " . implode(', ', $info['tenants']) . "\n\n";
}

echo "📊 GESAMT: {$totalRows} Einträge werden gelöscht\n\n";

// Bestätigung einholen
echo str_repeat('=', 80) . "\n";
echo "⚠️  WARNUNG: Diese Aktion kann NICHT rückgängig gemacht werden!\n";
echo "⚠️  Nur Demo-Daten werden gelöscht, COMET-Daten bleiben erhalten!\n";
echo str_repeat('=', 80) . "\n\n";

echo "Möchten Sie fortfahren und ALLE Demo-Daten löschen? (ja/nein): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$answer = trim(strtolower($line));
fclose($handle);

if ($answer !== 'ja' && $answer !== 'yes' && $answer !== 'y') {
    echo "\n❌ Abgebrochen. Keine Daten wurden gelöscht.\n";
    exit(0);
}

// Lösche die Daten
echo "\n" . str_repeat('=', 80) . "\n";
echo "🗑️  LÖSCHE DEMO-DATEN...\n";
echo str_repeat('=', 80) . "\n\n";

$deleted = 0;
$errors = [];
$deletedRows = 0;

foreach ($allDataToDelete as $item) {
    $tenantId = $item['tenant'];
    $dbName = $item['db'];
    $table = $item['table'];
    $count = $item['count'];

    try {
        // Verbindung zur Tenant-DB
        $tenantPdo = new PDO(
            "mysql:host=127.0.0.1;dbname={$dbName};charset=utf8mb4",
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Daten löschen (TRUNCATE)
        $tenantPdo->exec("TRUNCATE TABLE `{$table}`");

        echo "   ✅ {$tenantId}.{$table} - {$count} Einträge gelöscht\n";
        $deleted++;
        $deletedRows += $count;

    } catch (PDOException $e) {
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

echo "✅ Erfolgreich geleert: {$deleted}/" . count($allDataToDelete) . " Tabellen\n";
echo "✅ Gelöschte Einträge: {$deletedRows}\n";

if (!empty($errors)) {
    echo "❌ Fehler: " . count($errors) . "\n\n";
    echo "Details:\n";
    foreach ($errors as $error) {
        echo "   • {$error['tenant']}.{$error['table']}: {$error['error']}\n";
    }
} else {
    echo "\n🎉 Alle Demo-Daten erfolgreich gelöscht!\n";
    echo "ℹ️  Tabellen-Struktur bleibt erhalten, nur Daten wurden entfernt.\n";
}
