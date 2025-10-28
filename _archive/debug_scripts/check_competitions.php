<?php
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$competitions = \App\Models\CometCompetition::get();
echo "Total competitions: " . $competitions->count() . "\n";
echo "Organisations:\n";
$organisations = $competitions->groupBy('organisation_fifa_id')->keys();
foreach ($organisations as $org) {
    $comp = $competitions->where('organisation_fifa_id', $org)->first();
    $count = $competitions->where('organisation_fifa_id', $org)->count();
    $status = $comp->status;
    echo "  - $org (Status: $status, Count: $count)\n";
}

echo "\nSearching for NK Prigorje (should be FIFA ID related to 598)...\n";
$prigorje = $competitions->filter(function($c) {
    return str_contains(strtolower($c->international_name ?? ''), 'prigorje') ||
           str_contains(strtolower($c->international_name ?? ''), 'zagreb');
});

if ($prigorje->count() > 0) {
    echo "Found " . $prigorje->count() . " competitions:\n";
    foreach ($prigorje->take(5) as $c) {
        echo "  - {$c->comet_fifa_id}: {$c->international_name} (Org: {$c->organisation_fifa_id}, Status: {$c->status})\n";
    }
} else {
    echo "No competitions found\n";
    echo "\nSample competitions:\n";
    foreach ($competitions->take(5) as $c) {
        echo "  - {$c->comet_fifa_id}: {$c->international_name} (Org: {$c->organisation_fifa_id})\n";
    }
}
