<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔌 SEARCHING FOR ALL COMPETITIONS OF NK PRIGORJE (FIFA ID 598)\n";
echo "   Target: 11 Active Competitions\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // First, let's see ALL competitions for club 598 (without status filter)
    echo "📋 STEP 1: All competitions for club_fifa_id 598 (any status):\n";
    echo str_repeat("─", 80) . "\n\n";

    $allComps = DB::connection('mysql')
        ->table('kp_server.competitions')
        ->where('club_fifa_id', '598')
        ->orderBy('season', 'desc')
        ->orderBy('international_competition_name')
        ->get();

    echo "Total: " . count($allComps) . "\n\n";

    foreach ($allComps as $i => $comp) {
        echo "  [" . ($i + 1) . "] " . ($comp->international_competition_name ?? 'Unknown') .
             " | Season: " . ($comp->season ?? 'N/A') . " | Status: " . ($comp->status ?? 'N/A') . "\n";
    }
    echo "\n\n";

    // Now get only ACTIVE
    echo str_repeat("═", 80) . "\n";
    echo "📋 STEP 2: ONLY ACTIVE competitions for club_fifa_id 598:\n";
    echo str_repeat("─", 80) . "\n\n";

    $activeComps = DB::connection('mysql')
        ->table('kp_server.competitions')
        ->where('club_fifa_id', '598')
        ->where('status', 'ACTIVE')
        ->orderBy('season', 'desc')
        ->orderBy('international_competition_name')
        ->get();

    echo "Total ACTIVE: " . count($activeComps) . "\n\n";

    foreach ($activeComps as $i => $comp) {
        echo "【" . ($i + 1) . "】 " . ($comp->international_competition_name ?? 'Unknown') . "\n";
        echo "     FIFA ID: " . ($comp->competition_fifa_id ?? 'N/A') . "\n";
        echo "     Season: " . ($comp->season ?? 'N/A') . "\n";
        echo "     Age Category: " . ($comp->age_category_name ?? $comp->age_category ?? 'N/A') . "\n";
        echo "     Status: " . ($comp->status ?? 'N/A') . "\n";
        echo "     Created: " . ($comp->created_at ?? 'N/A') . "\n";
        echo "\n";
    }

    // If not 11, let's check different seasons
    if (count($activeComps) < 11) {
        echo str_repeat("═", 80) . "\n";
        echo "⚠️  Only found " . count($activeComps) . ", checking different seasons/statuses...\n";
        echo str_repeat("─", 80) . "\n\n";

        // Check what seasons exist
        $seasons = DB::connection('mysql')
            ->table('kp_server.competitions')
            ->where('club_fifa_id', '598')
            ->selectRaw('DISTINCT season')
            ->orderBy('season', 'desc')
            ->get();

        echo "Available seasons: ";
        foreach ($seasons as $s) {
            echo $s->season . " ";
        }
        echo "\n\n";

        // Check different statuses
        $statuses = DB::connection('mysql')
            ->table('kp_server.competitions')
            ->where('club_fifa_id', '598')
            ->selectRaw('DISTINCT status')
            ->get();

        echo "Available statuses: ";
        foreach ($statuses as $st) {
            echo $st->status . " ";
        }
        echo "\n\n";

        // Try to get more with broader criteria
        echo str_repeat("─", 80) . "\n";
        echo "Trying with season >= 2024 and ACTIVE status:\n";
        echo str_repeat("─", 80) . "\n\n";

        $broader = DB::connection('mysql')
            ->table('kp_server.competitions')
            ->where('club_fifa_id', '598')
            ->where('status', 'ACTIVE')
            ->where('season', '>=', '2024')
            ->orderBy('season', 'desc')
            ->orderBy('international_competition_name')
            ->get();

        echo "Total found: " . count($broader) . "\n\n";

        foreach ($broader as $i => $comp) {
            echo "  [" . ($i + 1) . "] " . ($comp->international_competition_name ?? 'Unknown') . "\n";
        }
        echo "\n";
    }

    echo str_repeat("═", 80) . "\n";
    echo "✅ Search complete\n";
    echo str_repeat("═", 80) . "\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n\n";
}
