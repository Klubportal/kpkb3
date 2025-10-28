<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Settings Tabelle ===\n\n";

$settings = DB::table('settings')->get();

echo "Anzahl der Settings: " . $settings->count() . "\n\n";

foreach ($settings as $setting) {
    echo sprintf(
        "ID: %d | Group: %-10s | Name: %-25s | Payload: %s\n",
        $setting->id,
        $setting->group,
        $setting->name,
        substr($setting->payload, 0, 50)
    );
}
