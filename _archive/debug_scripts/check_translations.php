<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LanguageLine;

echo "========================================\n";
echo "   ÜBERSETZUNGEN IN DER DATENBANK\n";
echo "========================================\n\n";

$total = LanguageLine::count();
echo "Gesamt Übersetzungsschlüssel: {$total}\n\n";

$sample = LanguageLine::first();
if ($sample) {
    $languages = array_keys($sample->text);
    $languageCount = count($languages);

    echo "Sprachen pro Eintrag: {$languageCount}\n";
    echo "Verfügbare Sprachen: " . implode(', ', $languages) . "\n\n";

    echo "Beispiel-Eintrag:\n";
    echo "  Group: {$sample->group}\n";
    echo "  Key: {$sample->key}\n";
    echo "  Übersetzungen:\n";
    foreach ($sample->text as $locale => $translation) {
        echo "    {$locale}: {$translation}\n";
    }
}

echo "\n========================================\n";
echo "   STATISTIK NACH GRUPPEN\n";
echo "========================================\n\n";

$groups = LanguageLine::selectRaw('`group`, COUNT(*) as count')
    ->groupBy('group')
    ->orderBy('count', 'desc')
    ->get();

foreach ($groups as $group) {
    echo sprintf("  %-30s %4d Einträge\n", $group->group . ':', $group->count);
}

echo "\n========================================\n";
