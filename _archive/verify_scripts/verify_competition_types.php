<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;

echo "【 Competition Types Verification 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$competitions = CometCompetition::orderBy('competition_fifa_id')->get();

echo "Competition | Type | Age Category\n";
echo "────────────────────────────────────────────────────────────────\n";

foreach ($competitions as $idx => $comp) {
    $name = substr($comp->international_name, 0, 40);
    $type = $comp->competition_type ?? 'N/A';
    $age = $comp->age_category ?? 'N/A';
    printf("%2d. %-35s | %-15s | %s\n", $idx + 1, $name, $type, $age);
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "✅ Verification Complete\n";
