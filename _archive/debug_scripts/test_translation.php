<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== TRANSLATION DEBUG ===\n\n";

// Prüfe ob Translation in DB existiert
$translation = DB::connection('central')
    ->table('language_lines')
    ->where('group', 'site')
    ->where('key', 'description')
    ->first();

if ($translation) {
    echo "✅ Translation in Datenbank gefunden:\n";
    echo "   Group: {$translation->group}\n";
    echo "   Key: {$translation->key}\n\n";

    $texts = json_decode($translation->text, true);
    echo "📝 Übersetzungen:\n";
    foreach ($texts as $lang => $text) {
        echo "   $lang: $text\n";
    }
} else {
    echo "❌ Translation NICHT gefunden!\n";
}

echo "\n🔍 Test trans() Helper:\n";
app()->setLocale('de');
echo "   DE: " . trans('site.description') . "\n";

app()->setLocale('en');
echo "   EN: " . trans('site.description') . "\n";

app()->setLocale('hr');
echo "   HR: " . trans('site.description') . "\n";

echo "\n📂 Prüfe Blade Template:\n";
$bladePath = 'resources/views/livewire/central/landing-page.blade.php';
$content = file_get_contents($bladePath);

if (strpos($content, "__('site.description')") !== false) {
    echo "   ✅ {{ __('site.description') }} gefunden in Template\n";
} else {
    echo "   ❌ {{ __('site.description') }} NICHT gefunden in Template\n";
    if (strpos($content, 'site_description') !== false) {
        echo "   ⚠️  Noch alte site_description Variable gefunden!\n";
    }
}

echo "\n";
