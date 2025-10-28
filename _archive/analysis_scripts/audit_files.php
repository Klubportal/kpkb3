<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

echo "【 Audit: Redundante Scripts 】\n";
echo "════════════════════════════════════════════════════════════\n\n";

// Get all files
$syncFiles = collect(File::glob('sync_*.php'))->map(fn($f) => basename($f))->sort();
$testFiles = collect(File::glob('test_*.php'))->map(fn($f) => basename($f))->sort();
$checkFiles = collect(File::glob('check_*.php'))->map(fn($f) => basename($f))->sort();

echo "【 SYNC FILES (" . $syncFiles->count() . ") 】\n";
foreach ($syncFiles as $file) {
    $lines = count(file($file));
    echo "  • {$file} (" . $lines . " lines)\n";
}

echo "\n【 TEST FILES (" . $testFiles->count() . ") 】\n";
foreach ($testFiles as $file) {
    $lines = count(file($file));
    echo "  • {$file} (" . $lines . " lines)\n";
}

echo "\n【 CHECK FILES (" . $checkFiles->count() . ") 】\n";
foreach ($checkFiles as $file) {
    $lines = count(file($file));
    echo "  • {$file} (" . $lines . " lines)\n";
}

echo "\n【 EMPFEHLUNG: DIESE FILES SIND PRODUKTIV 】\n";
echo "════════════════════════════════════════════════════════════\n";

$productive = [
    'SYNC' => [
        'sync_prigorje.php' => 'Main: NK Prigorje (598) vollständiger Sync',
        'sync_match_events_all_matches.php' => 'Sync: Match Events für alle Matches',
    ],
    'TEST' => [
        'test_backend_full.php' => 'Vollständiger Backend Test - ALLES prüfen',
        'test_competitions.php' => 'Test: Competitions Tabelle (NEU)',
    ],
    'CHECK' => [
        'check_match_events_progress.php' => 'Check: Match Events Status',
    ],
];

foreach ($productive as $category => $files) {
    echo "\n【 {$category} - PRODUKTIV 】\n";
    foreach ($files as $file => $desc) {
        if (file_exists($file)) {
            echo "  ✅ {$file}\n";
            echo "     → {$desc}\n";
        } else {
            echo "  ❌ {$file} (NICHT GEFUNDEN!)\n";
        }
    }
}

echo "\n【 DIESE FILES KÖNNEN GELÖSCHT WERDEN 】\n";
$toDelete = [];

// Doppelte sync files
$syncDuplicates = [
    'sync_prigorje_real_data.php',
    'sync_from_comet_api.php',
    'sync_all_prigorje_comprehensive.php',
    'sync_all_players_from_teams.php',
    'sync_all_matches_from_comet_api.php',
    'sync_all_matches_all_teams.php',
    'sync_all_competitions.php',
    'sync_rankings_from_comet.php',
    'sync_competitions_club_598.php', // Neu, incomplete
];

$testDuplicates = [
    'test_comet_basic_auth.php',
    'test_comet_auth.php',
    'test_backend_status.php',
    'test_api_endpoints.php',
    'test_all_api_endpoints.php',
    'test_insert_matches.php',
];

$checkDuplicates = [
    'check_team_names.php',
    'check_tables.php',
    'check_real_data.php',
    'check_rankings_schema.php',
    'check_player_statistics_schema.php',
    'check_players_schema.php',
    'check_match_record.php',
    'check_match_events_schema.php',
    'check_match_columns.php',
    'check_matches_table.php',
    'check_kp_schema.php',
    'check_db_schema.php',
    'check_databases.php',
    'check_competitions_schema.php',
    'check_comet_data.php',
    'check_club_tables.php',
    'check_club_manager_db.php',
];

$deleteCount = 0;
echo "\nSYNC (zu löschen): " . count($syncDuplicates) . "\n";
foreach ($syncDuplicates as $file) {
    if (file_exists($file)) {
        echo "  🗑️  {$file}\n";
        $deleteCount++;
    }
}

echo "\nTEST (zu löschen): " . count($testDuplicates) . "\n";
foreach ($testDuplicates as $file) {
    if (file_exists($file)) {
        echo "  🗑️  {$file}\n";
        $deleteCount++;
    }
}

echo "\nCHECK (zu löschen): " . count($checkDuplicates) . "\n";
foreach ($checkDuplicates as $file) {
    if (file_exists($file)) {
        echo "  🗑️  {$file}\n";
        $deleteCount++;
    }
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "✅ Gesamt zu löschende Files: {$deleteCount}\n";
echo "\nFühre aus: php cleanup_old_files.php\n";
