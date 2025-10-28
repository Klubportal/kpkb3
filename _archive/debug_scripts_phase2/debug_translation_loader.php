<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== TRANSLATION LOADER DEBUG ===\n\n";

// Setze Sprache auf HR
app()->setLocale('hr');
echo "🌐 Aktuelle Sprache: " . app()->getLocale() . "\n\n";

// Test 1: Direct DB query
echo "📊 Test 1: Direkte Datenbankabfrage\n";
$translation = DB::connection('central')
    ->table('language_lines')
    ->where('group', 'site')
    ->where('key', 'description')
    ->first();

if ($translation) {
    $texts = json_decode($translation->text, true);
    echo "   ✅ In DB: " . ($texts['hr'] ?? 'NICHT GEFUNDEN') . "\n\n";
} else {
    echo "   ❌ Nicht in Datenbank gefunden!\n\n";
}

// Test 2: trans() helper
echo "📝 Test 2: trans() Helper\n";
$result = trans('site.description');
echo "   Ergebnis: $result\n";
if ($result === 'site.description') {
    echo "   ❌ Gibt Key zurück - Translation nicht geladen!\n\n";
} else {
    echo "   ✅ Translation gefunden\n\n";
}

// Test 3: __() helper
echo "📝 Test 3: __() Helper\n";
$result = __('site.description');
echo "   Ergebnis: $result\n";
if ($result === 'site.description') {
    echo "   ❌ Gibt Key zurück - Translation nicht geladen!\n\n";
} else {
    echo "   ✅ Translation gefunden\n\n";
}

// Test 4: Check if TranslationServiceProvider is loaded
echo "🔧 Test 4: Service Provider Check\n";
if (class_exists('Spatie\TranslationLoader\TranslationServiceProvider')) {
    echo "   ✅ Spatie TranslationLoader installiert\n";

    // Check if registered
    $providers = config('app.providers');
    $found = false;
    foreach ($providers as $provider) {
        if (str_contains($provider, 'TranslationServiceProvider')) {
            echo "   ✅ TranslationServiceProvider registriert: $provider\n";
            $found = true;
        }
    }
    if (!$found) {
        echo "   ❌ TranslationServiceProvider NICHT in config/app.php registriert!\n";
    }
} else {
    echo "   ❌ Spatie TranslationLoader NICHT installiert\n";
}

echo "\n";
