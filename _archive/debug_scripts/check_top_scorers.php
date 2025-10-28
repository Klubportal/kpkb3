<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
$domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', 'nknapijed.localhost')->first();
if ($domain) {
    tenancy()->initialize($domain->tenant);
}

echo "Structure of comet_players:\n";
echo "===========================\n\n";

$columns = DB::select('DESCRIBE comet_players');
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

echo "\n\nTop scorers for club 598:\n";
echo "=========================\n\n";

// Try to get top scorers by joining player_competition_stats with players
$topScorers = DB::table('comet_player_competition_stats as stats')
    ->join('comet_players as players', 'stats.player_id', '=', 'players.id')
    ->where('players.club_fifa_id', 598)
    ->orderBy('stats.goals', 'desc')
    ->select(
        DB::raw("CONCAT(players.first_name, ' ', players.last_name) as player_name"),
        'stats.goals'
    )
    ->limit(10)
    ->get();

foreach ($topScorers as $scorer) {
    echo "{$scorer->player_name}: {$scorer->goals} goals\n";
}
