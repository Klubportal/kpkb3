<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 MATCH STATUS DISTRIBUTION\n";
echo "============================================================\n\n";

// Check status column name first
$columns = DB::connection('tenant')->select("SHOW COLUMNS FROM comet_matches LIKE '%status%'");
echo "📋 Status columns found:\n";
foreach ($columns as $col) {
    echo "   - {$col->Field}: {$col->Type}\n";
}
echo "\n";

// Get distribution for ALL matches
$statusDistribution = DB::connection('tenant')
    ->table('comet_matches')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->orderBy('count', 'DESC')
    ->get();

echo "📊 ALLE MATCHES:\n";
$total = 0;
foreach ($statusDistribution as $stat) {
    echo "   {$stat->status}: {$stat->count}\n";
    $total += $stat->count;
}
echo "   TOTAL: {$total}\n\n";

// Get distribution for NK Prigorje matches only
$teamFifaId = 598;
$nkPrigorjeStatus = DB::connection('tenant')
    ->table('comet_matches')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->where(function($query) use ($teamFifaId) {
        $query->where('team_fifa_id_home', $teamFifaId)
              ->orWhere('team_fifa_id_away', $teamFifaId);
    })
    ->groupBy('status')
    ->orderBy('count', 'DESC')
    ->get();

echo "📊 NK PRIGORJE MATCHES (Team 598):\n";
$nkTotal = 0;
foreach ($nkPrigorjeStatus as $stat) {
    echo "   {$stat->status}: {$stat->count}\n";
    $nkTotal += $stat->count;
}
echo "   TOTAL: {$nkTotal}\n\n";

// Check how many phases we have
$phaseCount = DB::connection('tenant')
    ->table('comet_match_phases')
    ->count();

echo "📊 MATCH PHASES:\n";
echo "   Total phases: {$phaseCount}\n";
echo "   Expected (if 2 per finished match): " . ($statusDistribution->where('status', 'finished')->first()->count ?? 0) * 2 . "\n\n";

// Check if sync script is filtering correctly
$syncedMatches = DB::connection('tenant')
    ->table('comet_matches')
    ->where(function($query) use ($teamFifaId) {
        $query->where('team_fifa_id_home', $teamFifaId)
              ->orWhere('team_fifa_id_away', $teamFifaId);
    })
    ->where('status', 'finished')
    ->count();

echo "🎯 SYNC TARGET:\n";
echo "   NK Prigorje finished matches: {$syncedMatches}\n";
echo "   Expected phases: " . ($syncedMatches * 2) . "\n";
echo "   Current phases: {$phaseCount}\n";
echo "   Missing phases: " . (($syncedMatches * 2) - $phaseCount) . "\n";
