#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          COMET REST API - DATABASE VERIFICATION               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Check tables
$tables = [
    'competitions',
    'rankings',
    'matches',
    'match_events',
    'players',
    'player_competition_stats',
    'clubs_extended',
    'comet_syncs',
    'club_competitions'
];

echo "📊 Database Tables Status:\n";
echo str_repeat("─", 65) . "\n";

$all_exist = true;
foreach ($tables as $table) {
    $result = DB::select("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);
    $exists = $result[0]->{'COUNT(*)'} > 0;
    $status = $exists ? '✓ EXISTS' : '✗ MISSING';
    $symbol = $exists ? '✅' : '❌';
    printf("  %s %-35s %s\n", $symbol, $table, $status);
    if (!$exists) $all_exist = false;
}

echo str_repeat("─", 65) . "\n";

// Check models
echo "\n📦 Eloquent Models Status:\n";
echo str_repeat("─", 65) . "\n";

$models = [
    'Competition',
    'Ranking',
    'GameMatch',
    'MatchEvent',
    'Player',
    'PlayerCompetitionStat',
    'ClubExtended',
    'CometSync',
    'CompetitionRanking',
    'Club'
];

foreach ($models as $model) {
    $class = 'App\\Models\\' . $model;
    $exists = class_exists($class);
    $status = $exists ? '✓ LOADED' : '✗ MISSING';
    $symbol = $exists ? '✅' : '❌';
    printf("  %s %-35s %s\n", $symbol, $model, $status);
}

echo str_repeat("─", 65) . "\n";

// Summary
echo "\n";
if ($all_exist) {
    echo "✅ ALL SYSTEMS GO - Comet API database infrastructure ready!\n\n";
    echo "Next Steps:\n";
    echo "  1. Test API endpoints: php artisan tinker\n";
    echo "  2. Review documentation: COMET_DATABASE_SETUP_COMPLETE.md\n";
    echo "  3. Configure Comet API credentials\n";
    echo "  4. Run sync operations\n";
} else {
    echo "⚠️  Some tables are missing. Please run setup again.\n";
}

echo "\n";
