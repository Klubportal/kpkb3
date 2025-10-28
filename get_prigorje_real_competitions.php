<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔌 GET COMPETITIONS WHERE NK PRIGORJE PARTICIPATES\n";
echo "   (As home OR away team)\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // Get competitions where NK Prigorje is home or away team
    $competitions = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where(function($q) {
            $q->where('team_fifa_id_home', '598')
              ->orWhere('team_fifa_id_away', '598');
        })
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

    echo "📊 COMPETITIONS WHERE NK PRIGORJE (FIFA 598) PARTICIPATES: " . count($competitions) . "\n";
    echo str_repeat("─", 80) . "\n\n";

    foreach ($competitions as $i => $comp) {
        echo "【" . ($i + 1) . "】 " . ($comp->international_competition_name ?? 'Unknown') . "\n";
        echo "     FIFA ID: " . ($comp->competition_fifa_id ?? 'N/A') . "\n";
        echo "     Season: " . ($comp->season ?? 'N/A') . "\n";
        echo "     Age Category: " . ($comp->age_category_name ?? $comp->age_category ?? 'N/A') . "\n";
        echo "     Status: " . ($comp->competition_status ?? 'N/A') . "\n";
        echo "\n";
    }

    echo str_repeat("═", 80) . "\n";
    if (count($competitions) == 11) {
        echo "✅ PERFECT! Found exactly 11 competitions!\n";
    } else {
        echo "⚠️  Found " . count($competitions) . " competitions (expected 11)\n";
    }
    echo str_repeat("═", 80) . "\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
