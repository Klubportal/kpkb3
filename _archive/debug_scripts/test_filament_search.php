<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LanguageLine;
use Illuminate\Database\Eloquent\Builder;

echo "========================================\n";
echo "   FILAMENT SEARCH SIMULATION\n";
echo "========================================\n\n";

$search = 'accept';

echo "Simulating Filament search for: '{$search}'\n\n";

// Genau so wie Filament es macht - mit searchable() Query
$query = LanguageLine::query();

// Erste Spalte: group_and_key
$query->where(function($q) use ($search) {
    $q->where('group', 'like', "%{$search}%")
      ->orWhere('key', 'like', "%{$search}%")
      ->orWhereRaw("JSON_SEARCH(text, 'one', ?) IS NOT NULL", ["%{$search}%"]);
});

$results = $query->get();

echo "Gefundene Einträge: " . $results->count() . "\n\n";

if ($results->count() > 0) {
    echo "Erste 5 Ergebnisse:\n";
    foreach ($results->take(5) as $result) {
        echo "  - {$result->group}.{$result->key}\n";
        if (isset($result->text['de'])) {
            echo "    DE: {$result->text['de']}\n";
        }
    }
} else {
    echo "PROBLEM: Keine Ergebnisse gefunden!\n";
    echo "\nDebug-Info:\n";
    echo "Total records in table: " . LanguageLine::count() . "\n";
    echo "Sample record:\n";
    $sample = LanguageLine::first();
    if ($sample) {
        echo "  Group: {$sample->group}\n";
        echo "  Key: {$sample->key}\n";
        echo "  Text type: " . gettype($sample->text) . "\n";
        print_r($sample->text);
    }
}

echo "\n========================================\n";
