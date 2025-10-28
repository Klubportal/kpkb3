<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('nkprigorjem');

$settings = DB::table('template_settings')->first();

echo "=== TEMPLATE SETTINGS ===\n\n";

if ($settings) {
    foreach($settings as $key => $value) {
        if (str_contains($key, 'color') || 
            str_contains($key, 'logo') || 
            str_contains($key, 'slogan') || 
            str_contains($key, 'year') || 
            str_contains($key, 'name') ||
            str_contains($key, 'website')) {
            echo "$key = " . ($value ?? 'NULL') . "\n";
        }
    }
} else {
    echo "No settings found!\n";
}
