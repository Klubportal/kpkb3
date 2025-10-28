<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Central\Tenant;

$tenant = Tenant::find('testclub');

if (!$tenant) {
    echo "Tenant nicht gefunden!\n";
    exit(1);
}

echo "\n========================================\n";
echo "  THEME SETTINGS HINZUFÜGEN\n";
echo "========================================\n\n";

$tenant->run(function () {
    $themeSettings = [
        ['group' => 'theme', 'name' => 'header_bg_color', 'locked' => false, 'payload' => json_encode('#1e40af')],
        ['group' => 'theme', 'name' => 'footer_bg_color', 'locked' => false, 'payload' => json_encode('#1f2937')],
        ['group' => 'theme', 'name' => 'text_color', 'locked' => false, 'payload' => json_encode('#1f2937')],
        ['group' => 'theme', 'name' => 'link_color', 'locked' => false, 'payload' => json_encode('#2563eb')],
        ['group' => 'theme', 'name' => 'border_radius', 'locked' => false, 'payload' => json_encode('0.5rem')],
    ];

    foreach ($themeSettings as $setting) {
        $exists = DB::table('settings')
            ->where('group', $setting['group'])
            ->where('name', $setting['name'])
            ->exists();

        if (!$exists) {
            $setting['created_at'] = now();
            $setting['updated_at'] = now();
            DB::table('settings')->insert($setting);
            echo "✓ {$setting['name']}\n";
        } else {
            echo "- {$setting['name']} (existiert bereits)\n";
        }
    }
});

echo "\n========================================\n";
echo "Theme Settings hinzugefügt!\n";
echo "========================================\n\n";
