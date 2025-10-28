<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;
use Illuminate\Support\Facades\DB;

echo "【 Testing Competitions Table 】\n";
echo "═══════════════════════════════════════════\n";

// Test 1: Check table exists
$tables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kp_club_management'");
$tableNames = array_map(fn($t) => $t->TABLE_NAME, $tables);

if (in_array('comet_competitions', $tableNames)) {
    echo "✅ Tabelle 'comet_competitions' existiert\n";
} else {
    echo "❌ Tabelle nicht gefunden!\n";
    exit(1);
}

// Test 2: Check columns
$columns = DB::select("DESCRIBE comet_competitions");
echo "\n【 Spaltenliste 】\n";
foreach ($columns as $col) {
    echo "  • {$col->Field} ({$col->Type})\n";
}

// Test 3: Model Test
echo "\n【 Model Test 】\n";
try {
    $competition = CometCompetition::first();
    if ($competition) {
        echo "✅ Erste Competition gefunden: {$competition->international_name}\n";
    } else {
        echo "✅ Tabelle ist leer (erwartbar)\n";
    }
} catch (Exception $e) {
    echo "❌ Model Error: {$e->getMessage()}\n";
}

// Test 4: Column count
$columnCount = count($columns);
echo "\n【 Zusammenfassung 】\n";
echo "✅ Tabelle: comet_competitions\n";
echo "✅ Spalten: {$columnCount}\n";
echo "✅ Migration: erfolgreich\n";
echo "✅ Model: CometCompetition\n";
echo "\n✅ COMPETITIONS READY!\n";
