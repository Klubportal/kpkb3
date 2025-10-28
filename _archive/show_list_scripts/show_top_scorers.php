<?php
require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Load Laravel bootstrap
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Fetch top scorers
$topScorers = DB::table('top_scorers')
    ->join('comet_competitions', 'top_scorers.comet_competition_id', '=', 'comet_competitions.competition_fifa_id')
    ->select([
        'top_scorers.rank',
        'top_scorers.player_name',
        'top_scorers.team_name',
        'top_scorers.goals',
        'top_scorers.assists',
        'top_scorers.matches_played',
        'top_scorers.goals_per_match',
        'comet_competitions.internationalShortName as competition'
    ])
    ->orderBy('top_scorers.rank')
    ->get();

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "TOP SCORERS - PRVA ZAGREBAČKA LIGA - SENIORI 25/26\n";
echo str_repeat("=", 80) . "\n\n";

printf("%-4s %-25s %-20s %-7s %-7s %-6s %-7s\n", "Rank", "Player", "Team", "Goals", "Assists", "Matches", "Avg");
echo str_repeat("-", 80) . "\n";

foreach ($topScorers as $scorer) {
    printf(
        "%-4s %-25s %-20s %-7s %-7s %-6s %-7s\n",
        $scorer->rank,
        substr($scorer->player_name, 0, 24),
        substr($scorer->team_name, 0, 19),
        $scorer->goals,
        $scorer->assists,
        $scorer->matches_played,
        $scorer->goals_per_match
    );
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Total scorers: " . count($topScorers) . "\n";
echo "\n";
