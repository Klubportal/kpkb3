<?php
// Test sync of Comet data
require 'bootstrap/app.php';

$app = app();
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

$username = 'nkprigorje';
$password = '3c6nR$dS';
$auth = base64_encode("$username:$password");

echo "\n=== Testing API Data Availability ===\n\n";

// Get active competitions
$comps = DB::table('comet_competitions')->where('status', 'ACTIVE')->get();

foreach ($comps as $comp) {
    $comp_id = $comp->competition_fifa_id;
    echo "Competition: $comp_id - {$comp->internationalShortName}\n";

    // Test top scorers
    $response = Http::withHeaders([
        'Authorization' => "Basic $auth"
    ])->get("https://api-hns.analyticom.de/api/export/comet/competition/$comp_id/topScorers");

    echo "  Top Scorers: Status " . $response->status();
    if ($response->ok()) {
        $scorers = $response->json();
        echo " - Found " . count($scorers) . " scorers";
        if (!empty($scorers)) {
            echo "\n    Sample: {$scorers[0]['internationalFirstName']} {$scorers[0]['internationalLastName']} - {$scorers[0]['goals']} goals";
        }
    }
    echo "\n";

    // Test teams
    $response = Http::withHeaders([
        'Authorization' => "Basic $auth"
    ])->get("https://api-hns.analyticom.de/api/export/comet/competition/$comp_id/teams");

    echo "  Teams: Status " . $response->status();
    if ($response->ok()) {
        $teams = $response->json();
        echo " - Found " . count($teams) . " teams";
    }
    echo "\n";

    echo "\n";
    break; // Just test first competition
}

echo "=== End of Test ===\n\n";
