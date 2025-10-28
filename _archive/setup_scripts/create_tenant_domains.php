<?php

$pdo = new PDO('mysql:host=localhost;dbname=kpkb3;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== DOMAINS FÜR TENANTS ERSTELLEN ===\n\n";

// Hole alle Tenants
$stmt = $pdo->query("SELECT id, data FROM tenants");
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tenants)) {
    echo "⚠️  Keine Tenants in der Datenbank gefunden!\n";
    echo "Möglicherweise müssen Sie zuerst Tenants erstellen.\n";
    exit(1);
}

echo "Gefundene Tenants:\n";
echo str_repeat('-', 60) . "\n";

foreach ($tenants as $tenant) {
    $data = json_decode($tenant['data'], true);
    $tenantId = $tenant['id'];

    echo "Tenant ID: {$tenantId}\n";
    if (isset($data['club_name'])) {
        echo "  Club: {$data['club_name']}\n";
    }
    if (isset($data['subdomain'])) {
        echo "  Subdomain: {$data['subdomain']}\n";
    }
    echo "\n";
}

echo str_repeat('-', 60) . "\n\n";

// Prüfe bestehende Domains
$stmt = $pdo->query("SELECT domain, tenant_id FROM domains");
$existingDomains = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Bestehende Domains in der Tabelle:\n";
if (empty($existingDomains)) {
    echo "  (keine)\n\n";
} else {
    foreach ($existingDomains as $domain) {
        echo "  - {$domain['domain']} → {$domain['tenant_id']}\n";
    }
    echo "\n";
}

// Erstelle fehlende Domains
echo "Erstelle fehlende Domains...\n";
echo str_repeat('-', 60) . "\n";

$insertStmt = $pdo->prepare("INSERT INTO domains (domain, tenant_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
$created = 0;

foreach ($tenants as $tenant) {
    $data = json_decode($tenant['data'], true);
    $tenantId = $tenant['id'];

    if (!isset($data['subdomain'])) {
        echo "⚠️  Tenant {$tenantId} hat keine Subdomain - übersprungen\n";
        continue;
    }

    $subdomain = $data['subdomain'];
    $domain = $subdomain . '.localhost';

    // Prüfe ob Domain bereits existiert
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM domains WHERE domain = ?");
    $checkStmt->execute([$domain]);

    if ($checkStmt->fetchColumn() > 0) {
        echo "  ✓ {$domain} existiert bereits\n";
        continue;
    }

    // Erstelle Domain
    try {
        $insertStmt->execute([$domain, $tenantId]);
        echo "  ✅ {$domain} → {$tenantId} erstellt\n";
        $created++;
    } catch (PDOException $e) {
        echo "  ❌ Fehler bei {$domain}: {$e->getMessage()}\n";
    }
}

echo str_repeat('-', 60) . "\n";
echo "✅ {$created} neue Domain(s) erstellt\n\n";

// Zeige finale Übersicht
$stmt = $pdo->query("SELECT domain, tenant_id FROM domains ORDER BY domain");
$allDomains = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Finale Domain-Übersicht:\n";
echo str_repeat('-', 60) . "\n";
foreach ($allDomains as $domain) {
    echo "  {$domain['domain']} → {$domain['tenant_id']}\n";
}

echo "\n✅ Fertig! Tenants sollten jetzt über ihre Subdomains erreichbar sein.\n";
