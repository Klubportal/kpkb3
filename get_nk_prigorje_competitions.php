<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   NK PRIGORJE COMPETITIONS (Season 2025/2026)                ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Hole alle aktiven Competitions für Season 2025/2026
$competitions = DB::connection('central')
    ->table('comet_competitions')
    ->whereIn('season', [2025, 2026, '2025', '2026'])
    ->where('active', true)
    ->orderBy('season', 'desc')
    ->orderBy('name')
    ->get();

echo "📊 Total Active Competitions: " . $competitions->count() . "\n\n";

foreach ($competitions as $comp) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📌 " . $comp->name . "\n";
    echo "   Competition FIFA ID: " . $comp->comet_id . "\n";
    echo "   Season: " . $comp->season . " | Status: " . ($comp->active ? 'ACTIVE' : 'INACTIVE') . "\n";
    echo "   Organisation: " . $comp->organisation_fifa_id . " | Type: " . $comp->type . "\n";
    echo "   Gender: " . ($comp->gender ?? 'N/A') . " | Age Category: " . ($comp->age_category ?? 'N/A') . "\n";
    echo "   Team Character: " . ($comp->team_character ?? 'N/A') . " | Nature: " . ($comp->nature ?? 'N/A') . "\n";
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Query completed successfully!\n";
