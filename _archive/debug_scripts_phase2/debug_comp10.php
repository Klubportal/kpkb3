<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;

$comp10 = CometCompetition::where('competition_fifa_id', 101674511)->first();
echo "Exact name of comp #10: '" . $comp10->international_name . "'\n";
echo "Length: " . strlen($comp10->international_name) . "\n";

// Check substrings
$name = $comp10->international_name;
echo "Contains 'kup': " . (stripos($name, 'kup') !== false ? 'YES (position ' . stripos($name, 'kup') . ')' : 'NO') . "\n";
echo "Contains 'cup': " . (stripos($name, 'cup') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'liga': " . (stripos($name, 'liga') !== false ? 'YES (position ' . stripos($name, 'liga') . ')' : 'NO') . "\n";
echo "Contains 'prvenstvo': " . (stripos($name, 'prvenstvo') !== false ? 'YES' : 'NO') . "\n";

// Show first characters
echo "\nFirst 50 chars: " . substr($name, 0, 50) . "\n";
echo "Last 50 chars: " . substr($name, -50) . "\n";
