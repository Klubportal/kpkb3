<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking COMET Data for FIFA ID 396 ===\n\n";

echo "Total matches in central: " . DB::connection('central')->table('comet_matches')->count() . "\n";
echo "Matches with 396 as home: " . DB::connection('central')->table('comet_matches')->where('team_fifa_id_home', 396)->count() . "\n";
echo "Matches with 396 as away: " . DB::connection('central')->table('comet_matches')->where('team_fifa_id_away', 396)->count() . "\n\n";

echo "Total matches: " . DB::connection('central')->table('comet_matches')->count() . "\n";
echo "Unique home clubs: " . DB::connection('central')->table('comet_matches')->distinct()->count('team_fifa_id_home') . "\n";
echo "Unique away clubs: " . DB::connection('central')->table('comet_matches')->distinct()->count('team_fifa_id_away') . "\n\n";

echo "=== Sample of team FIFA IDs in matches ===\n";
$sample = DB::connection('central')->table('comet_matches')
    ->select('team_fifa_id_home', 'team_name_home', 'team_fifa_id_away', 'team_name_away')
    ->limit(10)
    ->get();

foreach ($sample as $match) {
    echo "Home: {$match->team_fifa_id_home} ({$match->team_name_home}) vs Away: {$match->team_fifa_id_away} ({$match->team_name_away})\n";
}

echo "\n=== Checking Players for 396 ===\n";
echo "Players with club_fifa_id 396: " . DB::connection('central')->table('comet_players')->where('club_fifa_id', 396)->count() . "\n";

echo "\n=== Looking for NK Naprijed in matches ===\n";
$naprijed = DB::connection('central')->table('comet_matches')
    ->where('team_name_home', 'like', '%Naprijed%')
    ->orWhere('team_name_away', 'like', '%Naprijed%')
    ->limit(5)
    ->get();

foreach ($naprijed as $match) {
    echo "Match: {$match->team_name_home} ({$match->team_fifa_id_home}) vs {$match->team_name_away} ({$match->team_fifa_id_away})\n";
}
