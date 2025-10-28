<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking active competitions being synced...\n";
echo "============================================\n\n";

// Get active competitions from central database
$competitions = DB::connection('mysql')->table('kpkb3.comet_competitions')
    ->where('active', 1)
    ->get();

echo "Active competitions (" . $competitions->count() . "):\n\n";

foreach ($competitions as $comp) {
    echo "ID: {$comp->comet_id}\n";
    echo "Name: {$comp->name}\n";
    if (isset($comp->season_name)) echo "Season: {$comp->season_name}\n";
    echo "Active: " . ($comp->active ? 'YES' : 'NO') . "\n";
    echo "---\n";
}

echo "\n\nChecking which clubs these competitions belong to...\n";
echo "====================================================\n\n";

// Get club competitions
$clubComps = DB::connection('mysql')->table('kpkb3.comet_club_competitions')
    ->get();

echo "Club Competitions (" . $clubComps->count() . "):\n\n";

$nknapijed = 0;
$nkprigorje = 0;

foreach ($clubComps as $cc) {
    if ($cc->club_fifa_id == 396) {
        $nknapijed++;
        echo "✓ NK Naprijed (396) - Competition: {$cc->competition_fifa_id}\n";
    } elseif ($cc->club_fifa_id == 598) {
        $nkprigorje++;
        echo "✓ NK Prigorje (598) - Competition: {$cc->competition_fifa_id}\n";
    }
}

echo "\n\nSummary:\n";
echo "========\n";
echo "NK Naprijed (396): {$nknapijed} competitions\n";
echo "NK Prigorje (598): {$nkprigorje} competitions\n";
