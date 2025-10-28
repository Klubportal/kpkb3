<?php

// Script: Entferne DB::connection('central') aus Tenant Migrationen

$tenantMigrationsPath = __DIR__ . '/database/migrations/tenant/';
$cometFiles = glob($tenantMigrationsPath . '2025_10_26_*_create_comet_*.php');

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔧 Fixe DB::connection('central') in Tenant-Migrationen\n";
echo str_repeat("═", 80) . "\n\n";

$fixedCount = 0;

foreach ($cometFiles as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    $originalContent = $content;

    // Ersetze DB::connection('central') mit DB
    $content = str_replace(
        "DB::connection('central')->statement",
        "DB::statement",
        $content
    );

    $content = str_replace(
        "DB::connection('central')->",
        "DB::",
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "✅ {$filename}\n";
        $fixedCount++;
    }
}

echo "\n" . str_repeat("═", 80) . "\n";
echo "✅ {$fixedCount} Dateien korrigiert!\n";
echo str_repeat("═", 80) . "\n\n";
