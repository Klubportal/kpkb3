<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LanguageLine;

echo "========================================\n";
echo "   TRANSLATION SEARCH TEST\n";
echo "========================================\n\n";

// Test 1: Suche nach "login"
echo "Test 1: Suche nach 'login' in group/key:\n";
$results = LanguageLine::where('group', 'like', '%login%')
    ->orWhere('key', 'like', '%login%')
    ->get();
echo "Gefunden: " . $results->count() . " Einträge\n";
foreach ($results->take(3) as $result) {
    echo "  - {$result->group}.{$result->key}\n";
}

echo "\n";

// Test 2: Suche nach "Akzeptieren" im JSON text field
echo "Test 2: Suche nach 'Akzeptieren' im text (JSON):\n";
$results = LanguageLine::whereRaw("JSON_SEARCH(text, 'one', ?) IS NOT NULL", ['%Akzeptieren%'])
    ->get();
echo "Gefunden: " . $results->count() . " Einträge\n";
foreach ($results->take(3) as $result) {
    echo "  - {$result->group}.{$result->key}\n";
    echo "    DE: " . ($result->text['de'] ?? 'N/A') . "\n";
}

echo "\n";

// Test 3: Kombinierte Suche nach "accept"
echo "Test 3: Kombinierte Suche nach 'accept':\n";
$results = LanguageLine::where(function($q) {
    $search = 'accept';
    $q->where('group', 'like', "%{$search}%")
      ->orWhere('key', 'like', "%{$search}%")
      ->orWhereRaw("JSON_SEARCH(text, 'one', ?) IS NOT NULL", ["%{$search}%"]);
})->get();
echo "Gefunden: " . $results->count() . " Einträge\n";
foreach ($results->take(5) as $result) {
    echo "  - {$result->group}.{$result->key}\n";
}

echo "\n========================================\n";
