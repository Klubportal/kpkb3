<?php

$pdo = new PDO('mysql:host=localhost;dbname=kpkb3;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== TENANT-EINTRÄGE WIEDERHERSTELLEN ===\n\n";

// Liste der existierenden Tenant-DBs
$tenantDatabases = [
    'tenant_nknapijed' => 'nknapijed',
    'tenant_nkprigorjem' => 'nkprigorjem',
];

echo "Gefundene Tenant-Datenbanken:\n";
foreach ($tenantDatabases as $db => $subdomain) {
    echo "  - {$db} ({$subdomain})\n";
}
echo "\n";

// Prüfe bestehende Tenant-Einträge
$stmt = $pdo->query("SELECT id FROM tenants");
$existingTenants = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Bestehende Tenant-Einträge: " . count($existingTenants) . "\n\n";

// Für jeden Tenant: Hole Informationen aus der Tenant-DB und erstelle Eintrag
$insertTenantStmt = $pdo->prepare("
    INSERT INTO tenants (id, data, created_at, updated_at)
    VALUES (?, ?, NOW(), NOW())
    ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = NOW()
");

$insertDomainStmt = $pdo->prepare("
    INSERT INTO domains (domain, tenant_id, created_at, updated_at)
    VALUES (?, ?, NOW(), NOW())
    ON DUPLICATE KEY UPDATE tenant_id = VALUES(tenant_id), updated_at = NOW()
");

echo "Wiederherstellung der Tenant-Einträge...\n";
echo str_repeat('-', 60) . "\n";

foreach ($tenantDatabases as $database => $subdomain) {
    echo "\n{$subdomain} ({$database}):\n";

    // Tenant-ID ist die Subdomain
    $tenantId = $subdomain;

    // Versuche Club-Namen aus tenant_db zu holen
    try {
        $tenantPdo = new PDO("mysql:host=localhost;dbname={$database};charset=utf8mb4", 'root', '');
        $tenantPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Hole Template Settings für Club-Namen
        $stmt = $tenantPdo->query("SELECT club_name FROM template_settings LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        $clubName = $settings['club_name'] ?? ucfirst($subdomain);

    } catch (PDOException $e) {
        echo "  ⚠️  Konnte Template Settings nicht laden: {$e->getMessage()}\n";
        $clubName = ucfirst($subdomain);
    }

    // Tenant-Data JSON
    $tenantData = json_encode([
        'subdomain' => $subdomain,
        'club_name' => $clubName,
        'tenancy_db_name' => $database,
    ]);

    // Erstelle Tenant-Eintrag
    try {
        $insertTenantStmt->execute([$tenantId, $tenantData]);
        echo "  ✅ Tenant-Eintrag: {$tenantId}\n";
        echo "     Club: {$clubName}\n";
    } catch (PDOException $e) {
        echo "  ❌ Fehler bei Tenant: {$e->getMessage()}\n";
        continue;
    }

    // Erstelle Domain-Eintrag
    $domain = $subdomain . '.localhost';
    try {
        $insertDomainStmt->execute([$domain, $tenantId]);
        echo "  ✅ Domain: {$domain}\n";
    } catch (PDOException $e) {
        echo "  ❌ Fehler bei Domain: {$e->getMessage()}\n";
    }
}

echo "\n" . str_repeat('-', 60) . "\n";

// Finale Übersicht
$stmt = $pdo->query("
    SELECT t.id, t.data, d.domain
    FROM tenants t
    LEFT JOIN domains d ON t.id = d.tenant_id
    ORDER BY t.id
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n✅ FINALE ÜBERSICHT:\n";
echo str_repeat('-', 60) . "\n";

foreach ($results as $row) {
    $data = json_decode($row['data'], true);
    echo "\nTenant: {$row['id']}\n";
    echo "  Club: " . ($data['club_name'] ?? 'N/A') . "\n";
    echo "  Domain: " . ($row['domain'] ?? 'FEHLT!') . "\n";
    echo "  DB: " . ($data['tenancy_db_name'] ?? 'N/A') . "\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Wiederherstellung abgeschlossen!\n";
echo "   Sie können jetzt auf die Tenants zugreifen:\n";
echo "   - http://nknapijed.localhost:8000/club\n";
echo "   - http://nkprigorjem.localhost:8000/club\n";
echo str_repeat('=', 60) . "\n";
