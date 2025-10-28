<?php
$c = new mysqli('localhost', 'root', '');

echo "🔍 Checking groups table in tenant_nkprigorjem...\n\n";

$r = $c->query("SHOW TABLES FROM tenant_nkprigorjem LIKE 'groups'");

if ($r->num_rows > 0) {
    echo "✅ groups table EXISTS\n\n";

    // Show structure
    echo "Structure:\n";
    echo "------------------------------------------------------------\n";
    $cols = $c->query("DESCRIBE tenant_nkprigorjem.groups");
    while($col = $cols->fetch_assoc()) {
        echo sprintf("%-20s %-25s %-8s %-8s %s\n",
            $col['Field'],
            $col['Type'],
            $col['Null'],
            $col['Key'],
            $col['Default'] ?? ''
        );
    }

    // Count records
    $count = $c->query("SELECT COUNT(*) as cnt FROM tenant_nkprigorjem.groups")->fetch_assoc();
    echo "\n📊 Total groups: {$count['cnt']}\n";

    if ($count['cnt'] > 0) {
        echo "\nExisting groups:\n";
        $groups = $c->query("SELECT id, name, label, gender, active, published FROM tenant_nkprigorjem.groups ORDER BY `order`");
        while($g = $groups->fetch_assoc()) {
            echo "  - ID {$g['id']}: {$g['name']} ({$g['label']}) - Gender: {$g['gender']}, Active: {$g['active']}, Published: {$g['published']}\n";
        }
    }

} else {
    echo "❌ groups table DOES NOT EXIST\n";
    echo "\n📋 Need to run migration:\n";
    echo "   php artisan tenants:migrate --tenants=nkprigorjem\n";
}

$c->close();
