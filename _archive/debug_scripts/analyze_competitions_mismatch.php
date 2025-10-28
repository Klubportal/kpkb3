<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ANALYSE: Welche Teams sind in den Competitions? ===\n\n";

// Get all competitions from comet_club_competitions
$clubComps = DB::connection('central')
    ->table('comet_club_competitions')
    ->get();

echo "1. Competitions in comet_club_competitions:\n";
echo str_repeat('-', 80) . "\n";

foreach ($clubComps as $comp) {
    $competitionDetails = DB::connection('central')
        ->table('comet_competitions')
        ->where('comet_id', $comp->competitionFifaId)
        ->first();

    if ($competitionDetails) {
        echo "Competition: {$competitionDetails->name}\n";
        echo "  FIFA ID: {$comp->competitionFifaId}\n";
        echo "  Season: {$comp->season}\n";
        echo "  Age Category: {$comp->ageCategory}\n\n";
    }
}

echo "\n2. Jetzt checke ich die API - welche Competitions hat Team FIFA ID 618?\n";
echo str_repeat('-', 80) . "\n";

// Use API to get competitions
$api = new \App\Services\CometApiService();

try {
    $apiCompetitions = $api->getCompetitions([
        'teamFifaId' => 618,
        'active' => true,
    ]);

    echo "API returned " . count($apiCompetitions) . " competitions\n\n";

    foreach ($apiCompetitions as $comp) {
        $season = $comp['season'] ?? '';
        if (!str_contains($season, '2025') && !str_contains($season, '2026')) {
            continue;
        }

        echo "- {$comp['internationalName']}\n";
        echo "  Competition FIFA ID: {$comp['competitionFifaId']}\n";
        echo "  Season: {$comp['season']}\n";
        echo "  Organisation: {$comp['organisationFifaId']}\n";
        echo "  Age Category: " . ($comp['ageCategory'] ?? 'N/A') . "\n\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. Check: Gibt es JUNIORI Competitions?\n";
echo str_repeat('-', 80) . "\n";

$juniorComps = DB::connection('central')
    ->table('comet_competitions')
    ->where('name', 'LIKE', '%JUNIOR%')
    ->orWhere('age_category', 'LIKE', '%JUNIOR%')
    ->get();

echo "Found " . $juniorComps->count() . " JUNIOR competitions in database\n\n";

foreach ($juniorComps as $comp) {
    echo "- {$comp->name}\n";
    echo "  FIFA ID: {$comp->comet_id}\n";
    echo "  Season: {$comp->season}\n";
    echo "  Age: {$comp->age_category}\n\n";
}
