<?php

echo "=== Tenant DB Migration (Direct SQL) ===\n\n";

$tenants = ['tenant_nkprigorjem', 'tenant_nknapijed'];

foreach ($tenants as $tenantDb) {
    echo "Migriere: {$tenantDb}\n";
    echo str_repeat("=", 50) . "\n";

    $mysqli = new mysqli('localhost', 'root', '', $tenantDb);

    if ($mysqli->connect_error) {
        echo "  ❌ Connection failed: " . $mysqli->connect_error . "\n\n";
        continue;
    }

    // Hole alle Tenant Migration-Dateien
    $migrationFiles = glob(__DIR__ . '/database/migrations/tenant/*.php');
    sort($migrationFiles);

    $executed = 0;
    $skipped = 0;

    foreach ($migrationFiles as $file) {
        $migrationName = basename($file, '.php');

        // Lese Migration-Datei
        $content = file_get_contents($file);

        // Extrahiere SQL aus DB::statement()
        if (preg_match('/DB::statement\(<<<SQL\s*(.*?)\s*SQL\s*\)/s', $content, $matches)) {
            $sql = trim($matches[1]);

            // Führe SQL aus
            try {
                if (@$mysqli->query($sql)) {
                    echo "  ✓ {$migrationName}\n";
                    $executed++;
                } else {
                    echo "  ⚠ {$migrationName} (bereits vorhanden)\n";
                    $skipped++;
                }
            } catch (Exception $e) {
                echo "  ⚠ {$migrationName} (bereits vorhanden)\n";
                $skipped++;
            }
        } else {
            echo "  ⚠ {$migrationName} (kein SQL gefunden)\n";
            $skipped++;
        }
    }

    echo "\n  Ausgeführt: {$executed} | Übersprungen: {$skipped}\n";
    echo "  ✅ {$tenantDb} migriert!\n\n";

    $mysqli->close();
}

echo "✅ Alle Tenant-DBs migriert!\n";
