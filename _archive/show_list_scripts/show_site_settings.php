<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== SITE EINSTELLUNGEN ===\n\n";

$settings = DB::connection('central')->table('settings')->where('group', 'general')->get();

foreach($settings as $setting) {
    $value = json_decode($setting->payload, true);
    echo "📌 {$setting->name}:\n";
    echo "   " . (is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value) . "\n\n";
}

echo "💡 LÖSUNG für mehrsprachige Beschreibung:\n";
echo "   Die Site-Beschreibung sollte einen Translation-Key verwenden\n";
echo "   Statt: 'Deine Fußballverein Management Platform'\n";
echo "   Besser: trans('site.description')\n\n";
