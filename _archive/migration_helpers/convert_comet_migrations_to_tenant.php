<?php

// Script: Konvertiere Central Migrationen zu Tenant Migrationen
// Entfernt Schema::connection('central') und ersetzt mit Schema

$tenantMigrationsPath = __DIR__ . '/database/migrations/tenant/';

$cometFiles = glob($tenantMigrationsPath . '2025_10_26_*_create_comet_*.php');

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔄 Konvertiere COMET Migrationen zu Tenant-Migrationen\n";
echo str_repeat("═", 80) . "\n\n";

echo "Gefundene Dateien: " . count($cometFiles) . "\n\n";

foreach ($cometFiles as $file) {
    $filename = basename($file);
    echo "📝 Bearbeite: {$filename}\n";

    // Lese Datei
    $content = file_get_contents($file);

    // Ersetze Schema::connection('central') mit Schema
    $originalContent = $content;
    $content = str_replace(
        "Schema::connection('central')->create",
        "Schema::create",
        $content
    );
    $content = str_replace(
        "Schema::connection('central')->dropIfExists",
        "Schema::dropIfExists",
        $content
    );

    // Prüfe ob etwas geändert wurde
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "   ✅ Konvertiert (Schema::connection('central') → Schema)\n";
    } else {
        echo "   ⏭️  Keine Änderungen nötig\n";
    }
}

echo "\n" . str_repeat("═", 80) . "\n";
echo "✅ Konvertierung abgeschlossen!\n";
echo str_repeat("═", 80) . "\n\n";

echo "📊 Zusammenfassung:\n";
echo "   - Central Migrationen (database/migrations/): Verwenden Schema::connection('central')\n";
echo "   - Tenant Migrationen (database/migrations/tenant/): Verwenden Schema (ohne connection)\n\n";

echo "🚀 Nächster Schritt:\n";
echo "   php artisan tenants:migrate\n\n";
