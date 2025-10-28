<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);

echo "\n=== TESTE ÜBERSETZUNGEN ===\n\n";

// Setze Locale auf HR
app()->setLocale('hr');
session(['locale' => 'hr']);

echo "🌐 Aktuelle Sprache: " . app()->getLocale() . "\n\n";

// Teste alle Nav-Übersetzungen
$navKeys = ['features', 'news', 'pricing', 'contact', 'login', 'register_club', 'language'];

echo "--- NAV Übersetzungen ---\n";
foreach ($navKeys as $key) {
    $translation = __("nav.{$key}");
    $db = \Illuminate\Support\Facades\DB::connection('central')
        ->table('language_lines')
        ->where('group', 'nav')
        ->where('key', $key)
        ->first();

    echo "nav.{$key}:\n";
    echo "  __() returns: {$translation}\n";
    if ($db) {
        $text = json_decode($db->text, true);
        echo "  DB (hr): " . ($text['hr'] ?? 'NICHT GEFUNDEN') . "\n";
    } else {
        echo "  DB: ❌ NICHT IN DB\n";
    }
    echo "\n";
}

// Teste Hero-Übersetzungen
echo "--- HERO Übersetzungen ---\n";
$heroKeys = ['start_free', 'learn_more'];
foreach ($heroKeys as $key) {
    $translation = __("hero.{$key}");
    $db = \Illuminate\Support\Facades\DB::connection('central')
        ->table('language_lines')
        ->where('group', 'hero')
        ->where('key', $key)
        ->first();

    echo "hero.{$key}:\n";
    echo "  __() returns: {$translation}\n";
    if ($db) {
        $text = json_decode($db->text, true);
        echo "  DB (hr): " . ($text['hr'] ?? 'NICHT GEFUNDEN') . "\n";
    } else {
        echo "  DB: ❌ NICHT IN DB\n";
    }
    echo "\n";
}

// Teste site.description
echo "--- SITE Übersetzungen ---\n";
$translation = __('site.description');
echo "site.description:\n";
echo "  __() returns: {$translation}\n";
$db = \Illuminate\Support\Facades\DB::connection('central')
    ->table('language_lines')
    ->where('group', 'site')
    ->where('key', 'description')
    ->first();
if ($db) {
    $text = json_decode($db->text, true);
    echo "  DB (hr): " . ($text['hr'] ?? 'NICHT GEFUNDEN') . "\n";
} else {
    echo "  DB: ❌ NICHT IN DB\n";
}

$kernel->terminate($request, $response);
