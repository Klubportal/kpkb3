<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔧 Marking problematic migrations as run...\n";
echo "============================================\n";

$problemMigrations = [
    '2025_10_26_162127_create_comet_rankings_table',
    '2025_10_26_171211_create_settings_table',
    '2025_10_26_173000_create_comet_top_scorers_final_table'
];

foreach ($problemMigrations as $migration) {
    try {
        DB::table('migrations')->updateOrInsert(
            ['migration' => $migration],
            ['migration' => $migration, 'batch' => 999]
        );
        echo "✅ Marked: {$migration}\n";
    } catch (Exception $e) {
        echo "❌ Error marking {$migration}: " . $e->getMessage() . "\n";
    }
}

echo "\n🎯 Done!\n";
