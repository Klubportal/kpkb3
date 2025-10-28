<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;

echo "【 Competitions with Croatian Age Category Labels 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Load Croatian age category translations
$ageCategoryLabelsHr = require 'config/age_category_labels_hr.php';

$competitions = CometCompetition::orderBy('competition_fifa_id')->get();

echo "Competition | Type | Age (EN) | Age (HR - Hrvatski)\n";
echo "─────────────────────────────────────────────────────────────────\n";

foreach ($competitions as $idx => $comp) {
    $name = substr($comp->international_name, 0, 35);
    $type = $comp->competition_type ?? 'N/A';
    $ageEn = $comp->age_category ?? 'N/A';
    $ageHr = $ageCategoryLabelsHr[$ageEn] ?? 'N/A';

    printf("%2d. %-32s | %-10s | %-8s | %s\n",
        $idx + 1,
        $name,
        $type,
        $ageEn,
        $ageHr
    );
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "✅ All Competitions with Croatian Age Category Labels\n";
