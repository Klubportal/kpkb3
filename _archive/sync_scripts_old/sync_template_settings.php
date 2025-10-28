<?php

/**
 * Synchronisiert template_settings von einem Quell-Tenant zu allen anderen Tenants
 *
 * Verwendung: php sync_template_settings.php [source_tenant]
 * Beispiel: php sync_template_settings.php nkprigorjem
 */

$sourceTenant = $argv[1] ?? 'nkprigorjem';
$sourceDb = 'tenant_' . $sourceTenant;

$tenantDbs = [
    'tenant_nknapijed' => 'NK Naprijed',
    'tenant_nkprigorjem' => 'NK Prigorje',
];

// Entferne Source-DB aus der Liste
unset($tenantDbs[$sourceDb]);

if (empty($tenantDbs)) {
    echo "Keine Ziel-Tenants gefunden!\n";
    exit(1);
}

echo "=== TEMPLATE SETTINGS SYNCHRONISIERUNG ===\n\n";
echo "Quelle: {$sourceDb}\n";
echo "Ziele:  " . implode(', ', array_keys($tenantDbs)) . "\n\n";

try {
    // Hole Settings von der Quell-DB
    $sourcePdo = new PDO("mysql:host=localhost;dbname={$sourceDb};charset=utf8mb4", 'root', '');
    $sourcePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $sourcePdo->query("SELECT * FROM template_settings WHERE id = 1");
    $sourceSettings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sourceSettings) {
        echo "❌ Keine template_settings in {$sourceDb} gefunden!\n";
        exit(1);
    }

    // Entferne ID und club-spezifische Felder
    unset($sourceSettings['id']);
    unset($sourceSettings['created_at']);
    $clubSpecificFields = ['website_name', 'slogan', 'footer_about'];

    echo "Geladene Settings von {$sourceDb}:\n";
    echo str_repeat('-', 60) . "\n";
    foreach ($sourceSettings as $key => $value) {
        if (in_array($key, $clubSpecificFields)) {
            echo "  {$key}: [wird angepasst]\n";
        } else {
            $display = $value === null ? 'NULL' : (is_numeric($value) ? $value : substr($value, 0, 50));
            echo "  {$key}: {$display}\n";
        }
    }
    echo str_repeat('-', 60) . "\n\n";

    // Synchronisiere zu allen Ziel-Tenants
    foreach ($tenantDbs as $targetDb => $targetLabel) {
        echo "Synchronisiere zu {$targetLabel} ({$targetDb})...\n";

        try {
            $targetPdo = new PDO("mysql:host=localhost;dbname={$targetDb};charset=utf8mb4", 'root', '');
            $targetPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Hole aktuelle club-spezifische Werte
            $stmt = $targetPdo->query("SELECT website_name, slogan, footer_about FROM template_settings WHERE id = 1");
            $clubData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($clubData) {
                // Behalte club-spezifische Daten
                foreach ($clubSpecificFields as $field) {
                    if (isset($clubData[$field])) {
                        $sourceSettings[$field] = $clubData[$field];
                    }
                }
            }

            // Baue UPDATE Query
            $setParts = [];
            $values = [];
            foreach ($sourceSettings as $key => $value) {
                if ($key !== 'updated_at') {
                    $setParts[] = "`{$key}` = ?";
                    $values[] = $value;
                }
            }
            $setParts[] = "`updated_at` = NOW()";

            $sql = "UPDATE template_settings SET " . implode(', ', $setParts) . " WHERE id = 1";
            $stmt = $targetPdo->prepare($sql);
            $stmt->execute($values);

            echo "  ✅ {$stmt->rowCount()} Zeile(n) aktualisiert\n\n";

        } catch (PDOException $e) {
            echo "  ❌ Fehler: {$e->getMessage()}\n\n";
        }
    }

    echo str_repeat('=', 60) . "\n";
    echo "✅ Synchronisierung abgeschlossen!\n";
    echo "\nHINWEIS: Club-spezifische Felder wurden NICHT überschrieben:\n";
    echo "  - website_name\n";
    echo "  - slogan\n";
    echo "  - footer_about\n";
    echo str_repeat('=', 60) . "\n";

} catch (PDOException $e) {
    echo "❌ Fehler: {$e->getMessage()}\n";
    exit(1);
}
