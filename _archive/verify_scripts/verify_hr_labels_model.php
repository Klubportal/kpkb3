<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;

echo "【 Competitions with Model Accessor - Croatian Labels 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$competitions = CometCompetition::orderBy('competition_fifa_id')->get();

echo "Competition | Type | Age Category (EN) | Age Category (HR - Hrvatski)\n";
echo "──────────────────────────────────────────────────────────────────\n";

foreach ($competitions as $idx => $comp) {
    $name = substr($comp->international_name, 0, 32);
    $type = $comp->competition_type ?? 'N/A';
    $ageEn = $comp->age_category ?? 'N/A';
    $ageHr = $comp->age_category_label_hr ?? 'N/A'; // Using model accessor

    printf("%2d. %-30s | %-13s | %-16s | %s\n",
        $idx + 1,
        $name,
        $type,
        $ageEn,
        $ageHr
    );
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "✅ All Competitions with Croatian Age Category Labels (from Model Accessor)\n";
