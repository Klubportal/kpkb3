<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CLUB 598 COMPETITIONS - KORREKTE ZUORDNUNG ===\n\n";

// Check comet_club_competitions table
$clubComps = DB::connection('central')
    ->table('comet_club_competitions')
    ->get();

echo "1. Total entries in comet_club_competitions: " . $clubComps->count() . "\n\n";

if ($clubComps->count() > 0) {
    // Get unique competition IDs
    $competitionIds = $clubComps->pluck('competitionFifaId')->unique();

    echo "2. Unique Competition FIFA IDs: " . $competitionIds->count() . "\n\n";

    // Get competition details from comet_competitions
    $competitions = DB::connection('central')
        ->table('comet_competitions')
        ->whereIn('comet_id', $competitionIds)
        ->where('active', true)
        ->whereIn('season', [2025, 2026])
        ->orderBy('season', 'desc')
        ->orderBy('name')
        ->get();

    echo "3. AKTIVE Competitions (Season 2025/2026) für NK Prigorje:\n";
    echo "   Anzahl: " . $competitions->count() . "\n\n";

    echo "   Competitions:\n";
    echo "   " . str_repeat('-', 70) . "\n";

    foreach ($competitions as $comp) {
        echo "   [{$comp->comet_id}] {$comp->name}\n";
        echo "   Season: {$comp->season} | Type: {$comp->type}\n";
        if ($comp->gender) {
            echo "   Gender: {$comp->gender} | Age: {$comp->age_category}\n";
        }
        echo "\n";
    }

    echo "\n" . str_repeat('=', 70) . "\n";
    echo "✅ BESTÄTIGT: Club FIFA ID 598 nimmt an {$competitions->count()} Competitions teil\n";
    echo str_repeat('=', 70) . "\n";

} else {
    echo "❌ FEHLER: Keine Einträge in comet_club_competitions gefunden!\n";
}
