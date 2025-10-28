<?php

// Schnelles Script zum Einfügen der Theme Settings in Tenant DB

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Tenant initialisieren
$tenant = \App\Models\Central\Tenant::where('id', 'testclub')->first();

if (!$tenant) {
    echo "Tenant 'testclub' nicht gefunden!\n";
    exit(1);
}

tenancy()->initialize($tenant);

// Settings einfügen
$settings = [
    ['group' => 'theme', 'name' => 'active_theme', 'locked' => 0, 'payload' => json_encode('default')],
    ['group' => 'theme', 'name' => 'header_bg_color', 'locked' => 0, 'payload' => json_encode('#dc2626')],
    ['group' => 'theme', 'name' => 'footer_bg_color', 'locked' => 0, 'payload' => json_encode('#1f2937')],
    ['group' => 'theme', 'name' => 'text_color', 'locked' => 0, 'payload' => json_encode('#1f2937')],
    ['group' => 'theme', 'name' => 'link_color', 'locked' => 0, 'payload' => json_encode('#2563eb')],
    ['group' => 'theme', 'name' => 'button_style', 'locked' => 0, 'payload' => json_encode('rounded')],
    ['group' => 'theme', 'name' => 'dark_mode_enabled', 'locked' => 0, 'payload' => json_encode(false)],
    ['group' => 'theme', 'name' => 'layout_style', 'locked' => 0, 'payload' => json_encode('full-width')],
    ['group' => 'theme', 'name' => 'font_family', 'locked' => 0, 'payload' => json_encode('inter')],
    ['group' => 'theme', 'name' => 'border_radius', 'locked' => 0, 'payload' => json_encode('md')],
    ['group' => 'theme', 'name' => 'sidebar_width', 'locked' => 0, 'payload' => json_encode('normal')],
];

$inserted = 0;
foreach ($settings as $setting) {
    $exists = DB::table('settings')
        ->where('group', $setting['group'])
        ->where('name', $setting['name'])
        ->exists();

    if (!$exists) {
        DB::table('settings')->insert($setting);
        $inserted++;
        echo "✓ {$setting['name']} eingefügt\n";
    } else {
        echo "- {$setting['name']} existiert bereits\n";
    }
}

echo "\n✅ Fertig! {$inserted} Settings hinzugefügt\n";
