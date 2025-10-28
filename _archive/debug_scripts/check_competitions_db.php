<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;

echo "【 Active Competitions for Club 598 - Database Check 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$competitions = CometCompetition::where('status', 'ACTIVE')
    ->orderBy('competition_fifa_id')
    ->get(['competition_fifa_id', 'international_name', 'international_short_name', 'age_category', 'gender', 'season', 'status', 'date_from', 'date_to']);

echo "✅ Total Active Competitions: " . count($competitions) . "\n\n";

$counter = 0;
foreach ($competitions as $comp) {
    $counter++;
    echo "{$counter}. " . $comp->international_name . "\n";
    echo "   FIFA ID: " . $comp->competition_fifa_id . "\n";
    echo "   Age Category: " . $comp->age_category . " | Gender: " . $comp->gender . "\n";
    echo "   Season: " . $comp->season . "\n";
    echo "   Dates: " . $comp->date_from . " to " . $comp->date_to . "\n";
    echo "   Status: " . $comp->status . "\n\n";
}

echo "════════════════════════════════════════════════════════════════\n";
echo "\n💾 Database Query Complete\n";
