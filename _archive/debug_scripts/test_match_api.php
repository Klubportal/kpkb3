<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CometApiService;
use Illuminate\Support\Facades\DB;

$api = new CometApiService();

echo "=== TESTE MATCH API ===\n\n";

// Hole erste Competition
$comp = DB::connection('central')
    ->table('comet_club_competitions')
    ->first();

echo "Competition: {$comp->internationalName}\n";
echo "Competition FIFA ID: {$comp->competitionFifaId}\n\n";

try {
    $matches = $api->getCompetitionMatches($comp->competitionFifaId);

    echo "Total Matches: " . count($matches) . "\n\n";

    if (count($matches) > 0) {
        $firstMatch = $matches[0];

        echo "First Match Structure:\n";
        echo json_encode($firstMatch, JSON_PRETTY_PRINT) . "\n\n";

        echo "Home Team ID: " . ($firstMatch['homeTeam']['teamFifaId'] ?? 'NULL') . "\n";
        echo "Away Team ID: " . ($firstMatch['awayTeam']['teamFifaId'] ?? 'NULL') . "\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
