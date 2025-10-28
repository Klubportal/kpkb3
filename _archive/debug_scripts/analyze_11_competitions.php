<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CometApiService;

$api = new CometApiService();

$teamFifaId = 618;

echo "=== NK PRIGORJE COMPETITIONS ANALYSE ===\n\n";

$competitions = $api->getCompetitions([
    'teamFifaId' => $teamFifaId,
    'active' => true,
]);

echo "API gibt " . count($competitions) . " Competitions zurück für Team FIFA ID 618\n\n";

// Gruppiere nach Organisation
$byOrganisation = [];
$byType = [];

foreach ($competitions as $comp) {
    $season = $comp['season'] ?? '';
    if (!str_contains($season, '2026')) {
        continue;
    }

    $orgId = $comp['organisationFifaId'] ?? 'Unknown';
    $name = $comp['internationalName'];

    if (!isset($byOrganisation[$orgId])) {
        $byOrganisation[$orgId] = [];
    }
    $byOrganisation[$orgId][] = $name;

    // Kategorisiere
    if (str_contains(strtoupper($name), 'JUNIORI') || str_contains(strtoupper($name), 'JUNIOR')) {
        $category = 'JUNIORI';
    } elseif (str_contains(strtoupper($name), 'KADETI')) {
        $category = 'KADETI';
    } elseif (str_contains(strtoupper($name), 'PIONIRI') || str_contains(strtoupper($name), 'PIONIR')) {
        $category = 'PIONIRI';
    } elseif (str_contains(strtoupper($name), 'LIMAČ') || str_contains(strtoupper($name), 'ZAGIĆ')) {
        $category = 'MLAĐI (U9-U11)';
    } elseif (str_contains(strtoupper($name), 'SENIORI')) {
        $category = 'SENIORI';
    } elseif (str_contains(strtoupper($name), 'KUP')) {
        $category = 'KUP';
    } else {
        $category = 'OSTALO';
    }

    if (!isset($byType[$category])) {
        $byType[$category] = [];
    }
    $byType[$category][] = $name;
}

echo "COMPETITIONS NACH ORGANISATION:\n";
echo str_repeat('=', 80) . "\n";
foreach ($byOrganisation as $org => $comps) {
    echo "\nOrganisation $org: " . count($comps) . " Competitions\n";
    foreach ($comps as $name) {
        echo "  - $name\n";
    }
}

echo "\n\nCOMPETITIONS NACH KATEGORIE:\n";
echo str_repeat('=', 80) . "\n";
foreach ($byType as $type => $comps) {
    echo "\n$type: " . count($comps) . " Competitions\n";
    foreach ($comps as $name) {
        echo "  - $name\n";
    }
}

echo "\n\nWELCHE 11 COMPETITIONS MEINST DU?\n";
echo str_repeat('=', 80) . "\n";
echo "Bitte nenne mir die Namen der 11 Competitions, die NK Prigorje wirklich hat.\n";
echo "Dann kann ich die richtigen filtern.\n";
