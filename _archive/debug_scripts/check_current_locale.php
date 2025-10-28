<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simuliere Request
$request = Illuminate\Http\Request::create('/landing', 'GET');
$response = $kernel->handle($request);

echo "\n=== LOCALE CHECK ===\n\n";
echo "Current Locale: " . app()->getLocale() . "\n";
echo "Fallback Locale: " . config('app.fallback_locale') . "\n";
echo "Available Locales: " . implode(', ', config('app.available_locales', [])) . "\n";

// Teste mit HR
app()->setLocale('hr');
echo "\n--- Nach setLocale('hr') ---\n";
echo "Current Locale: " . app()->getLocale() . "\n";
echo "Translation: " . __('site.description') . "\n";

// Teste mit DE
app()->setLocale('de');
echo "\n--- Nach setLocale('de') ---\n";
echo "Current Locale: " . app()->getLocale() . "\n";
echo "Translation: " . __('site.description') . "\n";

// Prüfe Session
echo "\n--- Session Check ---\n";
$sessionLocale = session('locale');
echo "Session Locale: " . ($sessionLocale ?? 'NOT SET') . "\n";

// Prüfe mcamara/laravel-localization Config
echo "\n--- LaravelLocalization Config ---\n";
echo "Supported Locales: " . json_encode(config('laravellocalization.supportedLocales')) . "\n";
echo "UseAcceptLanguageHeader: " . (config('laravellocalization.useAcceptLanguageHeader') ? 'true' : 'false') . "\n";
echo "HideDefaultLocaleInURL: " . (config('laravellocalization.hideDefaultLocaleInURL') ? 'true' : 'false') . "\n";

$kernel->terminate($request, $response);
