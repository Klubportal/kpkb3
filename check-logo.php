<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$settings = app(\App\Settings\GeneralSettings::class);

echo "Logo: " . ($settings->logo ?? 'NULL') . PHP_EOL;
echo "Site Name: " . ($settings->site_name ?? 'NULL') . PHP_EOL;
echo "Logo Height: " . ($settings->logo_height ?? 'NULL') . PHP_EOL;

if ($settings->logo) {
    echo "\nLogo Path: " . $settings->logo . PHP_EOL;
    echo "Storage URL: " . \Storage::url($settings->logo) . PHP_EOL;
    echo "Asset Path: " . asset('storage/' . $settings->logo) . PHP_EOL;

    $fullPath = storage_path('app/public/' . $settings->logo);
    echo "Full Path: " . $fullPath . PHP_EOL;
    echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . PHP_EOL;
}
