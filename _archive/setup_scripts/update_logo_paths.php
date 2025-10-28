<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('nkprigorjem');

// Update logo paths from club-logos to logos
$updated = DB::table('template_settings')
    ->where('logo', 'like', 'club-logos/%')
    ->update([
        'logo' => DB::raw("REPLACE(logo, 'club-logos/', 'logos/')")
    ]);

echo "✅ Logo-Pfade aktualisiert: $updated Zeilen\n\n";

// Show current logo
$settings = DB::table('template_settings')->first();
echo "Aktuelles Logo: " . ($settings->logo ?? 'NULL') . "\n";
