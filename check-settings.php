<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$settings = app(\App\Settings\GeneralSettings::class);

echo "Site Name: " . ($settings->site_name ?? 'NULL') . PHP_EOL;
echo "Logo: " . ($settings->logo ?? 'NULL') . PHP_EOL;
echo "Favicon: " . ($settings->favicon ?? 'NULL') . PHP_EOL;
echo "Logo Height: " . ($settings->logo_height ?? 'NULL') . PHP_EOL;

echo "\n--- URLs ---\n";

if ($settings->logo) {
    echo "Logo Storage URL: " . \Storage::url($settings->logo) . PHP_EOL;
    echo "Logo exists: " . (file_exists(storage_path('app/public/' . $settings->logo)) ? 'YES' : 'NO') . PHP_EOL;
}

if ($settings->favicon) {
    echo "Favicon Storage URL: " . \Storage::url($settings->favicon) . PHP_EOL;
    echo "Favicon exists: " . (file_exists(storage_path('app/public/' . $settings->favicon)) ? 'YES' : 'NO') . PHP_EOL;
}
