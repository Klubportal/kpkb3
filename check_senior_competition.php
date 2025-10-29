<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Initialize tenant
tenancy()->initialize('nknapijed');

echo "=== Comet Club Competitions ===\n\n";

$competitions = \App\Models\Comet\CometClubCompetition::all();

foreach ($competitions as $comp) {
    echo "FIFA ID: {$comp->competitionFifaId}\n";
    echo "Name: {$comp->internationalName}\n";
    echo "Age Category: {$comp->ageCategory}\n";
    echo "Age Category Name: {$comp->ageCategoryName}\n";
    echo "Season: {$comp->season}\n";
    echo "Status: {$comp->status}\n";
    echo "Played Matches: {$comp->flag_played_matches}\n";
    echo "Scheduled Matches: {$comp->flag_scheduled_matches}\n";
    echo "---\n\n";
}

echo "\nTotal competitions: " . $competitions->count() . "\n\n";

// Check SENIORS data
echo "=== SENIORS Data Check ===\n\n";

$seniorsMatches = \App\Models\Comet\CometMatch::where('age_category', 'SENIORS')->count();
$seniorsRankings = \App\Models\Comet\CometRanking::where('age_category', 'SENIORS')->count();
$seniorsTopScorers = \App\Models\Comet\CometTopScorer::where('age_category', 'SENIORS')->count();

echo "SENIORS Matches: {$seniorsMatches}\n";
echo "SENIORS Rankings: {$seniorsRankings}\n";
echo "SENIORS Top Scorers: {$seniorsTopScorers}\n\n";

if ($seniorsRankings === 0) {
    echo "⚠️ WARNING: No SENIORS rankings found!\n";
    echo "   All rankings:\n";
    $allRankings = \App\Models\Comet\CometRanking::select('age_category', 'international_competition_name')->distinct()->get();
    foreach ($allRankings as $r) {
        echo "   - Age Category: " . ($r->age_category ?? 'NULL') . " | Competition: " . $r->international_competition_name . "\n";
    }
    echo "\n   Total rankings records: " . \App\Models\Comet\CometRanking::count() . "\n";
}

// Show some SENIORS matches
if ($seniorsMatches > 0) {
    echo "\n=== Sample SENIORS Matches ===\n\n";
    $sampleMatches = \App\Models\Comet\CometMatch::where('age_category', 'SENIORS')->limit(3)->get();
    foreach ($sampleMatches as $match) {
        echo "Date: {$match->date_time_local}\n";
        echo "Competition: {$match->international_competition_name}\n";
        echo "Match: {$match->team_name_home} vs {$match->team_name_away}\n";
        echo "---\n";
    }
}

// Check one ranking record
echo "\n=== Sample Ranking Record ===\n\n";
$sampleRanking = DB::connection('tenant')->table('comet_rankings')->first();
if ($sampleRanking) {
    print_r($sampleRanking);
} else {
    echo "No rankings found\n";
}

// Check Central DB rankings for competition IDs
echo "\n=== Central DB Rankings for Club 396 Competitions ===\n\n";
$centralRankings = DB::connection('central')
    ->table('comet_rankings')
    ->whereIn('competition_fifa_id', [100434823, 100663430, 101142607])
    ->get();
echo "Found: " . $centralRankings->count() . " rankings\n";
if ($centralRankings->count() > 0) {
    $first = $centralRankings->first();
    echo "Sample:\n";
    print_r($first);
}
