<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔌 FIND ALL 11 COMPETITIONS - EXTENDED SEARCH\n";
echo "   Including all statuses and checking duplicates\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // Get ALL distinct competitions from matches table for club 598
    $allCompetitions = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where('club_fifa_id', '598')
        ->selectRaw('DISTINCT
            competition_fifa_id,
            international_competition_name,
            season,
            age_category,
            age_category_name,
            competition_status')
        ->orderBy('season', 'desc')
        ->orderBy('international_competition_name')
        ->get();

    echo "📊 ALL COMPETITIONS (any status): " . count($allCompetitions) . "\n\n";

    foreach ($allCompetitions as $i => $comp) {
        echo "【" . ($i + 1) . "】 " . ($comp->international_competition_name ?? 'Unknown') . "\n";
        echo "     FIFA ID: " . ($comp->competition_fifa_id ?? 'N/A') . "\n";
        echo "     Season: " . ($comp->season ?? 'N/A') . "\n";
        echo "     Status: " . ($comp->competition_status ?? 'N/A') . "\n";
        echo "\n";
    }

    echo str_repeat("═", 80) . "\n";

    // Now let's check if status 'ACTIVE' means what we need
    $activeOnly = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where('club_fifa_id', '598')
        ->where('competition_status', 'ACTIVE')
        ->selectRaw('DISTINCT competition_status')
        ->get();

    echo "Distinct competition statuses:\n";
    $statuses = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where('club_fifa_id', '598')
        ->selectRaw('DISTINCT competition_status, COUNT(*) as count')
        ->groupBy('competition_status')
        ->get();

    foreach ($statuses as $status) {
        echo "  - " . $status->competition_status . ": " . $status->count . " records\n";
    }
    echo "\n";

    // Try with all statuses
    echo str_repeat("═", 80) . "\n";
    echo "Trying with ALL statuses (not just ACTIVE):\n";
    echo str_repeat("─", 80) . "\n\n";

    $allWithAllStatuses = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where('club_fifa_id', '598')
        ->selectRaw('DISTINCT
            competition_fifa_id,
            international_competition_name,
            season,
            age_category,
            age_category_name,
            competition_status')
        ->orderBy('season', 'desc')
        ->orderBy('international_competition_name')
        ->get();

    echo "Total distinct: " . count($allWithAllStatuses) . "\n\n";

    if (count($allWithAllStatuses) == 11) {
        echo "✅ PERFECT! Found exactly 11!\n\n";
    }

    foreach ($allWithAllStatuses as $i => $comp) {
        echo "【" . ($i + 1) . "】 " . ($comp->international_competition_name ?? 'Unknown') . "\n";
        echo "     FIFA: " . ($comp->competition_fifa_id ?? 'N/A') . " | Status: " . ($comp->competition_status ?? 'N/A') . "\n";
        echo "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo str_repeat("═", 80) . "\n";
