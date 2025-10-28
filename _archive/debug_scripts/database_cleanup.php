<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🗑️  Database Cleanup\n\n";

// 1. Alte sync_logs löschen (>90 Tage)
$oldLogs = DB::connection('central')->table('sync_logs')
    ->where('created_at', '<', now()->subDays(90))
    ->count();

if ($oldLogs > 0) {
    $deleted = DB::connection('central')->table('sync_logs')
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
    echo "✅ Gelöscht: {$deleted} alte sync_logs (>90 Tage)\n";
} else {
    echo "✅ Keine alten sync_logs gefunden (alle < 90 Tage)\n";
}

// 2. Statistik
echo "\n📊 Datenbank-Statistik:\n";
echo "   - sync_logs: " . DB::connection('central')->table('sync_logs')->count() . "\n";
echo "   - comet_matches: " . DB::connection('central')->table('comet_matches')->count() . "\n";
echo "   - comet_rankings: " . DB::connection('central')->table('comet_rankings')->count() . "\n";
echo "   - comet_top_scorers: " . DB::connection('central')->table('comet_top_scorers')->count() . "\n";
echo "   - comet_club_competitions: " . DB::connection('central')->table('comet_club_competitions')->count() . "\n";

echo "\n✅ Database Cleanup abgeschlossen\n";
