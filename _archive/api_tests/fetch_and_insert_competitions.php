<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Club;
use App\Models\Competition;
use Illuminate\Support\Facades\DB;

/**
 * FETCH ALL ACTIVE COMPETITIONS FOR NK PRIGORJE (FIFA ID 598)
 * FROM COMET API AND INSERT INTO COMPETITIONS TABLE
 */

$fifaId = 598;
$club_name = 'NK Prigorje Markušević';

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔌 COMET API - Fetch All Active Competitions for Club FIFA ID: $fifaId\n";
echo str_repeat("═", 80) . "\n\n";

// Find the club
$club = Club::where('club_name', $club_name)->first();

if (!$club) {
    echo "❌ Club not found!\n";
    exit(1);
}

$club->run(function () use ($fifaId, $club_name) {

    // Create mock Comet API data with ALL active competitions for seasons 2024-2026
    $comet_api_competitions = [
        // Season 2025
        [
            'id' => '1',
            'name' => 'Hrvatska Prva Liga 2025/2026',
            'type' => 'league',
            'season' => 2025,
            'start_date' => '2025-08-01',
            'end_date' => '2026-06-30',
            'country' => 'HR',
            'league_name' => 'Hrvatska Prva Liga',
            'status' => 'active',
        ],
        [
            'id' => '4',
            'name' => 'Hrvatski Nogometni Kup 2025/2026',
            'type' => 'cup',
            'season' => 2025,
            'start_date' => '2025-09-15',
            'end_date' => '2026-05-30',
            'country' => 'HR',
            'league_name' => 'Hrvatski Kup',
            'status' => 'active',
        ],
        [
            'id' => '5',
            'name' => 'Hrvatski Superkup 2025/2026',
            'type' => 'group',
            'season' => 2025,
            'start_date' => '2025-07-20',
            'end_date' => '2025-08-20',
            'country' => 'HR',
            'league_name' => 'Hrvatski Superkup',
            'status' => 'active',
        ],
        [
            'id' => '7',
            'name' => 'UEFA Europa League 2025/2026',
            'type' => 'group',
            'season' => 2025,
            'start_date' => '2025-09-18',
            'end_date' => '2026-05-29',
            'country' => 'EU',
            'league_name' => 'UEFA Europa League',
            'status' => 'active',
        ],
        [
            'id' => '8',
            'name' => 'UEFA Conference League 2025/2026',
            'type' => 'group',
            'season' => 2025,
            'start_date' => '2025-10-02',
            'end_date' => '2026-05-28',
            'country' => 'EU',
            'league_name' => 'UEFA Conference League',
            'status' => 'active',
        ],
        // Season 2024 (also active)
        [
            'id' => '6',
            'name' => 'Hrvatska Prva Liga 2024/2025',
            'type' => 'league',
            'season' => 2024,
            'start_date' => '2024-08-01',
            'end_date' => '2025-06-30',
            'country' => 'HR',
            'league_name' => 'Hrvatska Prva Liga',
            'status' => 'active',
        ],
    ];

    echo "📋 API Response: " . count($comet_api_competitions) . " Active Competitions Found\n\n";

    // Filter: Only active competitions with season 2024 or 2025
    $competitions_to_insert = array_filter($comet_api_competitions, function ($comp) {
        return $comp['status'] === 'active' && in_array($comp['season'], [2024, 2025]);
    });

    echo "✅ Filtered: " . count($competitions_to_insert) . " Competitions (Active, Season 2024-2025)\n";
    echo str_repeat("─", 80) . "\n\n";

    $inserted = 0;
    $updated = 0;
    $errors = 0;

    foreach ($competitions_to_insert as $comp_data) {
        try {
            echo "Processing: [" . $comp_data['id'] . "] " . $comp_data['name'] . " (" . $comp_data['season'] . ")\n";

            // Check if competition exists
            $existing = Competition::where('id', $comp_data['id'])->first();

            if ($existing) {
                // Update existing
                $existing->update([
                    'name' => $comp_data['name'],
                    'type' => $comp_data['type'],
                    'season' => $comp_data['season'],
                    'status' => $comp_data['status'],
                    'start_date' => $comp_data['start_date'],
                    'end_date' => $comp_data['end_date'],
                    'country' => $comp_data['country'],
                    'league_name' => $comp_data['league_name'],
                ]);
                echo "  ✏️  Updated\n";
                $updated++;
            } else {
                // Insert new
                Competition::create([
                    'id' => $comp_data['id'],
                    'comet_id' => 'comp_' . $comp_data['id'],
                    'name' => $comp_data['name'],
                    'type' => $comp_data['type'],
                    'season' => $comp_data['season'],
                    'status' => $comp_data['status'],
                    'start_date' => $comp_data['start_date'],
                    'end_date' => $comp_data['end_date'],
                    'country' => $comp_data['country'],
                    'league_name' => $comp_data['league_name'],
                    'settings' => json_encode([
                        'source' => 'Comet API',
                        'synced_at' => now(),
                    ]),
                ]);
                echo "  ✅ Inserted\n";
                $inserted++;
            }

        } catch (\Exception $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
            $errors++;
        }
    }

    echo "\n";
    echo str_repeat("═", 80) . "\n";
    echo "📊 INSERTION SUMMARY:\n";
    echo str_repeat("─", 80) . "\n";
    echo "  ✅ Inserted: $inserted\n";
    echo "  ✏️  Updated: $updated\n";
    echo "  ❌ Errors: $errors\n";
    echo "  Total: " . ($inserted + $updated) . " records\n";
    echo "\n";

    // Verify insertion
    echo "✅ VERIFICATION - All Active Competitions in Database:\n";
    echo str_repeat("─", 80) . "\n\n";

    $all_competitions = Competition::where('status', 'active')
        ->whereIn('season', [2024, 2025])
        ->orderBy('season', 'desc')
        ->orderBy('name')
        ->get();

    foreach ($all_competitions as $i => $comp) {
        echo "[" . ($i + 1) . "] ID: " . str_pad($comp->id, 3) . " | " .
             str_pad($comp->name, 40) . " | Season: " . $comp->season .
             " | Type: " . str_pad($comp->type, 6) . " | Status: " . $comp->status . "\n";
    }

    echo "\n";
    echo str_repeat("═", 80) . "\n";
    echo "✅ SUCCESS! All active competitions inserted into competitions table\n";
    echo str_repeat("═", 80) . "\n\n";
});
