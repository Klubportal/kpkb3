<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Expected comet tables
$expectedTables = [
    'comet_competitions',
    'comet_clubs_extended',
    'comet_rankings',
    'comet_matches',
    'comet_match_events',
    'comet_players',
    'comet_player_competition_stats',
    'comet_club_competitions',
    'comet_syncs',
    'comet_match_phases',
    'comet_match_players',
    'comet_match_officials',
    'comet_match_team_officials',
    'comet_team_officials',
    'comet_facilities',
    'comet_facility_fields',
    'comet_cases',
    'comet_sanctions',
    'comet_own_goal_scorers',
];

$tenants = \App\Models\Tenant::all();

echo "=== COMET-TABELLEN STATUS FÜR ALLE TENANTS ===\n\n";

foreach ($tenants as $tenant) {
    echo "Tenant: {$tenant->id}\n";
    echo str_repeat('-', 50) . "\n";

    try {
        tenancy()->initialize($tenant);

        // Get all tables
        $tables = DB::connection('tenant')->select('SHOW TABLES');
        $existingTables = [];

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            if (str_starts_with($tableName, 'comet_')) {
                $existingTables[] = $tableName;
            }
        }

        $missing = array_diff($expectedTables, $existingTables);

        echo "Vorhanden: " . count($existingTables) . "/" . count($expectedTables) . "\n";

        if (empty($missing)) {
            echo "✅ Alle Comet-Tabellen vorhanden!\n";
        } else {
            echo "❌ Fehlend (" . count($missing) . "):\n";
            foreach ($missing as $table) {
                echo "  - $table\n";
            }
        }

    } catch (\Exception $e) {
        echo "❌ Fehler: " . $e->getMessage() . "\n";
    }

    echo "\n";
}
