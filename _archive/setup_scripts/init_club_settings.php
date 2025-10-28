<?php

// Initialisiere Club Settings für Tenant testclub

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Initialisiere Tenancy für testclub
$domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', 'testclub.localhost')->first();
if (!$domain) {
    die("❌ Domain testclub.localhost nicht gefunden!\n");
}

tenancy()->initialize($domain->tenant);

echo "🎯 Initialisiere Club Settings für: {$domain->tenant->id}\n";

// Alle Settings mit Default-Werten
$settings = [
    ['group' => 'club', 'name' => 'club_name', 'payload' => json_encode('NK Prigorje')],
    ['group' => 'club', 'name' => 'club_full_name', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'club_slogan', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'founded_year', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'logo', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'logo_dark', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'favicon', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'header_image', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'primary_color', 'payload' => json_encode('#3b82f6')],
    ['group' => 'club', 'name' => 'secondary_color', 'payload' => json_encode('#64748b')],
    ['group' => 'club', 'name' => 'accent_color', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'font_family', 'payload' => json_encode('Inter')],
    ['group' => 'club', 'name' => 'font_size', 'payload' => json_encode(16)],
    ['group' => 'club', 'name' => 'contact_email', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'phone', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'address', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'city', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'postal_code', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'country', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'facebook_url', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'instagram_url', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'twitter_url', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'youtube_url', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'tiktok_url', 'payload' => json_encode(null)],
    ['group' => 'club', 'name' => 'show_sponsors', 'payload' => json_encode(true)],
    ['group' => 'club', 'name' => 'show_news', 'payload' => json_encode(true)],
    ['group' => 'club', 'name' => 'show_calendar', 'payload' => json_encode(true)],
    ['group' => 'club', 'name' => 'timezone', 'payload' => json_encode('Europe/Berlin')],
    ['group' => 'club', 'name' => 'locale', 'payload' => json_encode('de')],
];

$inserted = 0;
$skipped = 0;

foreach ($settings as $setting) {
    $exists = DB::connection('tenant')
        ->table('settings')
        ->where('group', $setting['group'])
        ->where('name', $setting['name'])
        ->exists();

    if (!$exists) {
        $setting['created_at'] = now();
        $setting['updated_at'] = now();
        $setting['locked'] = false;

        DB::connection('tenant')->table('settings')->insert($setting);
        $inserted++;
        echo "✅ {$setting['name']}\n";
    } else {
        $skipped++;
    }
}

echo "\n✅ Fertig! {$inserted} Settings eingefügt, {$skipped} übersprungen.\n";
echo "🎯 Öffne: http://testclub.localhost:8000/club/manage-club-settings\n";
