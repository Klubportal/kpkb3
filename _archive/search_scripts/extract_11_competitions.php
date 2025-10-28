<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔌 EXTRACT 11 COMPETITIONS FOR NK PRIGORJE FROM MATCHES TABLE\n";
echo "   Using: kp_server.matches table with distinct competitions\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // Get distinct competitions from matches table for club 598
    $competitions = DB::connection('mysql')
        ->table('kp_server.matches')
        ->where('club_fifa_id', '598')
        ->where('competition_status', 'ACTIVE')
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

    echo "📊 ACTIVE COMPETITIONS FOR NK PRIGORJE (FIFA ID 598): " . count($competitions) . "\n";
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
        echo "✅ PERFECT! Found exactly 11 active competitions!\n";
    } else {
        echo "⚠️  Found " . count($competitions) . " competitions (expected 11)\n";
    }
    echo str_repeat("═", 80) . "\n\n";

    // Export as JSON for insertion
    echo "📋 JSON FORMAT FOR DATABASE INSERTION:\n";
    echo str_repeat("─", 80) . "\n\n";

    $json_data = [];
    foreach ($competitions as $comp) {
        $json_data[] = [
            'comet_id' => 'comp_' . $comp->competition_fifa_id,
            'name' => $comp->international_competition_name,
            'season' => intval($comp->season),
            'status' => strtolower($comp->competition_status),
            'type' => 'league', // Default
            'country' => 'HR', // Default for Croatia
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    echo json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
