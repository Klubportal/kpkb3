<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔌 ALL ACTIVE COMPETITIONS FOR NK PRIGORJE (FIFA ID 598)\n";
echo "   From KP_SERVER Database\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // Query: Get all competitions for club_fifa_id 598
    $competitions = DB::connection('mysql')
        ->table('kp_server.competitions')
        ->where('club_fifa_id', '598')
        ->where('status', 'ACTIVE')
        ->orderBy('season', 'desc')
        ->get();

    echo "📊 TOTAL ACTIVE COMPETITIONS: " . count($competitions) . "\n";
    echo str_repeat("─", 80) . "\n\n";

    if (count($competitions) > 0) {
        foreach ($competitions as $i => $comp) {
            echo "【" . ($i + 1) . "】 " . ($comp->international_competition_name ?? 'Unknown') . "\n";
            echo "     Competition FIFA ID: " . ($comp->competition_fifa_id ?? 'N/A') . "\n";
            echo "     Club FIFA ID: " . ($comp->club_fifa_id ?? 'N/A') . "\n";
            echo "     Season: " . ($comp->season ?? 'N/A') . "\n";
            echo "     Age Category: " . ($comp->age_category_name ?? $comp->age_category ?? 'N/A') . "\n";
            echo "     Status: " . ($comp->status ?? 'N/A') . "\n";
            echo "     Created: " . ($comp->created_at ?? 'N/A') . "\n";
            echo "\n";
        }
    } else {
        echo "❌ No competitions found for club_fifa_id 598\n\n";

        // Show all available club_fifa_ids
        echo "📋 Available club_fifa_ids in competitions table:\n";
        echo str_repeat("─", 80) . "\n\n";

        $clubs = DB::connection('mysql')
            ->table('kp_server.competitions')
            ->selectRaw('DISTINCT club_fifa_id')
            ->orderBy('club_fifa_id')
            ->get();

        foreach ($clubs as $club) {
            $count = DB::connection('mysql')
                ->table('kp_server.competitions')
                ->where('club_fifa_id', $club->club_fifa_id)
                ->count();

            echo "  - Club FIFA ID: " . ($club->club_fifa_id ?? 'N/A') . " (" . $count . " competitions)\n";
        }
        echo "\n";
    }

    echo str_repeat("═", 80) . "\n";
    echo "✅ Comet data retrieved from kp_server database\n";
    echo str_repeat("═", 80) . "\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}
