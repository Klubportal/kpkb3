<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('nkprigorjem');

DB::table('template_settings')
    ->update([
        'website_name' => 'NK Prigorje Markuševec',
        'slogan' => 'Nogometni Klub Prigorje Markuševec',
    ]);

echo "✅ Vereinsname aktualisiert!\n\n";

$settings = DB::table('template_settings')->first();
echo "Website Name: {$settings->website_name}\n";
echo "Slogan: {$settings->slogan}\n";
