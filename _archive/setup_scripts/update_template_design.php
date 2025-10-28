<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('nkprigorjem');

// Beispiel: Logo-Pfad aktualisieren (wenn du ein Logo hast)
// Das Logo sollte in public/storage/logos/ liegen

$logoPath = 'logos/nk-prigorjem-logo.png'; // Beispiel

DB::table('template_settings')
    ->update([
        'logo' => $logoPath,
        'primary_color' => '#1e40af',      // Blau
        'secondary_color' => '#dc2626',    // Rot
        'accent_color' => '#f59e0b',       // Orange/Gold
        'header_bg_color' => '#1f2937',    // Dunkelgrau
        'footer_bg_color' => '#111827',    // Schwarz
        'show_logo' => true,
        'logo_height' => 60,
    ]);

echo "✅ Template-Einstellungen aktualisiert!\n\n";

// Zeige aktuelle Einstellungen
$settings = DB::table('template_settings')->first();

echo "=== AKTUELLE EINSTELLUNGEN ===\n\n";
echo "Website Name: {$settings->website_name}\n";
echo "Slogan: {$settings->slogan}\n";
echo "Logo: {$settings->logo}\n";
echo "Primärfarbe: {$settings->primary_color}\n";
echo "Sekundärfarbe: {$settings->secondary_color}\n";
echo "Akzentfarbe: {$settings->accent_color}\n";
echo "Header Farbe: {$settings->header_bg_color}\n";
echo "Footer Farbe: {$settings->footer_bg_color}\n";
