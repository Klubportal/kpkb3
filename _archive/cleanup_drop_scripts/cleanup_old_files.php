<?php

use Illuminate\Support\Facades\File;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "【 Cleanup: Redundante Files löschen 】\n";
echo "════════════════════════════════════════════════════════════\n\n";

$toDelete = [
    // SYNC duplicates
    'sync_prigorje_real_data.php',
    'sync_from_comet_api.php',
    'sync_all_prigorje_comprehensive.php',
    'sync_all_matches_from_comet_api.php',
    'sync_all_matches_all_teams.php',
    'sync_all_competitions.php',
    'sync_rankings_from_comet.php',
    'sync_competitions_club_598.php',
    // TEST duplicates
    'test_comet_basic_auth.php',
    'test_comet_auth.php',
    'test_backend_status.php',
    'test_api_endpoints.php',
    'test_all_api_endpoints.php',
    'test_insert_matches.php',
    // CHECK duplicates
    'check_team_names.php',
    'check_tables.php',
    'check_real_data.php',
    'check_rankings_schema.php',
    'check_player_statistics_schema.php',
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

$deleted = 0;
$notFound = 0;

foreach ($toDelete as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "  ✅ Gelöscht: {$file}\n";
        $deleted++;
    } else {
        echo "  ⚠️  Nicht gefunden: {$file}\n";
        $notFound++;
    }
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "✅ Gelöscht: {$deleted} Files\n";
echo "⚠️  Nicht gefunden: {$notFound} Files\n";

echo "\n【 VERBLEIBENDE PRODUKTIVE FILES 】\n";
echo "════════════════════════════════════════════════════════════\n\n";

$productive = [
    'sync_prigorje.php' => '🔄 Main Sync: NK Prigorje Daten laden',
    'sync_match_events_all_matches.php' => '🔄 Sync Match Events für alle Matches',
    'test_backend_full.php' => '✅ Backend Test: Gesamtsystem prüfen',
    'test_competitions.php' => '✅ Test: Competitions Tabelle',
    'check_match_events_progress.php' => '📊 Status: Match Events Progress',
    'audit_files.php' => '📋 Audit: File-Übersicht',
    'cleanup_old_files.php' => '🗑️  Cleanup: Redundante Files löschen',
];

foreach ($productive as $file => $desc) {
    if (file_exists($file)) {
        echo "  ✅ {$file}\n";
        echo "     → {$desc}\n\n";
    }
}

echo "════════════════════════════════════════════════════════════\n";
echo "✅ CLEANUP COMPLETE!\n";
