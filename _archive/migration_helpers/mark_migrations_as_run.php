<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Markiere Migrations als gelaufen ===\n\n";

// Hole alle Migration-Dateien
$migrations = [];
$batch = 1;

// Hauptordner
$mainFiles = glob(__DIR__ . '/database/migrations/*.php');
foreach ($mainFiles as $file) {
    $migrations[] = basename($file, '.php');
}

// Comet Ordner
$cometFiles = glob(__DIR__ . '/database/migrations/comet/*.php');
foreach ($cometFiles as $file) {
    $migrations[] = 'comet/' . basename($file, '.php');
}

sort($migrations);

echo "Gefundene Migrations: " . count($migrations) . "\n\n";

foreach ($migrations as $migration) {
    DB::table('migrations')->insert([
        'migration' => $migration,
        'batch' => $batch
    ]);
    echo "  ✓ {$migration}\n";
}

echo "\n✅ Alle Migrations als gelaufen markiert!\n";
echo "Batch: {$batch}\n";
