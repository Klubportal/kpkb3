<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Tenant initialisieren
$tenant = \App\Models\Tenant::find('nknapijed');
if (!$tenant) {
    die("Tenant nicht gefunden\n");
}

tenancy()->initialize($tenant);

echo "=== Storage URL Test ===\n\n";

$settings = \App\Models\Tenant\TemplateSetting::first();
if ($settings && $settings->logo) {
    echo "Logo in DB: {$settings->logo}\n";
    echo "Logo URL Accessor: {$settings->logo_url}\n\n";

    // Storage Disk prüfen
    $disk = \Storage::disk('public');
    echo "Storage Path: " . $disk->path('') . "\n";
    echo "File exists: " . ($disk->exists($settings->logo) ? 'YES' : 'NO') . "\n";

    if ($disk->exists($settings->logo)) {
        echo "File size: " . number_format($disk->size($settings->logo) / 1024, 2) . " KB\n";
        echo "Mime type: " . $disk->mimeType($settings->logo) . "\n";
    }

    // URL die generiert werden sollte
    echo "\nExpected URL: /storage/{$settings->logo}\n";
    echo "Full URL: http://nknapijed.localhost:8000/storage/{$settings->logo}\n";
} else {
    echo "Keine Template Settings gefunden!\n";
}
