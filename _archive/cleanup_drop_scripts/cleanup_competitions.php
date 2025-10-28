<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;
use Illuminate\Support\Facades\DB;

echo "【 Cleaning Duplicate Competitions 】\n";
echo "════════════════════════════════════════════════════════════════\n";

// Delete the old 6 competitions
$toDelete = [
    598001,  // Croatian Prva Liga 2025/2026
    598002,  // Croatian Kup 2025/2026
    598003,  // Zagreb City League 2025/2026
    598004,  // Croatian U-19 Championship 2025/2026
    598005,  // Croatian U-17 Championship 2025/2026
    598006,  // Croatian U-16 Championship 2025/2026
];

echo "\n【 Deleting Old Competitions 】\n";
foreach ($toDelete as $compId) {
    $comp = CometCompetition::find($compId);
    if ($comp) {
        $name = $comp->international_name;
        $comp->delete();
        echo "  🗑️  Deleted: {$name}\n";
    }
}

// Verify
echo "\n【 Verify Remaining Competitions 】\n";
$remaining = CometCompetition::orderBy('order_number')->get();

echo "✅ Total competitions: {$remaining->count()}\n\n";

$competitionsByLevel = [];
foreach ($remaining as $comp) {
    if (!isset($competitionsByLevel[$comp->age_category])) {
        $competitionsByLevel[$comp->age_category] = [];
    }
    $competitionsByLevel[$comp->age_category][] = $comp;
}

$totalCount = 0;
foreach ($competitionsByLevel as $level => $comps) {
    echo "【 {$level} - " . count($comps) . " competitions 】\n";
    foreach ($comps as $comp) {
        $totalCount++;
        echo "  {$totalCount}. {$comp->international_name}\n";
        echo "     ID: {$comp->competition_fifa_id} | {$comp->competition_type} | {$comp->nature}\n";
    }
    echo "\n";
}

echo "════════════════════════════════════════════════════════════════\n";
echo "✅ FINAL: {$remaining->count()} Active Competitions!\n";
