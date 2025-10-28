<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simuliere einen Request zur Landing Page
$request = Illuminate\Http\Request::create('/landing', 'GET');

// Starte Session
$request->setLaravelSession($app->make('session.store'));

echo "\n=== SESSION TEST ===\n\n";

// Test 1: Session ohne Locale
echo "1. Ohne Session Locale:\n";
$response = $kernel->handle($request);
echo "   App Locale: " . app()->getLocale() . "\n";
echo "   Session Locale: " . (session('locale') ?? 'NICHT GESETZT') . "\n\n";

// Test 2: Setze Session Locale auf 'hr'
echo "2. Setze Session auf 'hr':\n";
session(['locale' => 'hr']);
session()->save();
echo "   Session gesetzt auf: " . session('locale') . "\n\n";

// Test 3: Neuer Request mit gesetzter Session
echo "3. Neuer Request mit Session:\n";
$request2 = Illuminate\Http\Request::create('/landing', 'GET');
$request2->setLaravelSession($app->make('session.store'));
$response2 = $kernel->handle($request2);
echo "   App Locale: " . app()->getLocale() . "\n";
echo "   Session Locale: " . (session('locale') ?? 'NICHT GESETZT') . "\n";
echo "   Translation test: " . __('nav.features') . "\n\n";

echo "=== SESSION DRIVER ===\n";
echo "Driver: " . config('session.driver') . "\n";
echo "Lifetime: " . config('session.lifetime') . " Minuten\n";

$kernel->terminate($request, $response);
