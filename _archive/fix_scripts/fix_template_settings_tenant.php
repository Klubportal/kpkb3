<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Finde den Tenant
$tenant = \Stancl\Tenancy\Facades\Tenancy::find('nknapijed');

if (!$tenant) {
    die("Tenant nknapijed nicht gefunden!\n");
}

// Initialisiere Tenancy
tenancy()->initialize($tenant);

echo "Verbunden mit Tenant-Datenbank: " . config('database.connections.tenant.database') . "\n\n";

// Prüfe ob template_settings existiert
try {
    $exists = DB::connection('tenant')->select("SHOW TABLES LIKE 'template_settings'");

    if (empty($exists)) {
        echo "❌ Tabelle 'template_settings' existiert NICHT\n\n";

        echo "Erstelle template_settings Tabelle...\n";

        // Erstelle die Tabelle manuell
        DB::connection('tenant')->statement("
            CREATE TABLE `template_settings` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `website_name` varchar(255) NOT NULL DEFAULT 'Mein Verein',
                `slogan` varchar(255) NULL,
                `logo` varchar(255) NULL,
                `logo_height` int NOT NULL DEFAULT 50,
                `primary_color` varchar(255) NOT NULL DEFAULT '#1e40af',
                `secondary_color` varchar(255) NOT NULL DEFAULT '#dc2626',
                `accent_color` varchar(255) NOT NULL DEFAULT '#f59e0b',
                `header_bg_color` varchar(255) NOT NULL DEFAULT '#1f2937',
                `footer_bg_color` varchar(255) NOT NULL DEFAULT '#111827',
                `text_color` varchar(255) NOT NULL DEFAULT '#1f2937',
                `show_logo` tinyint(1) NOT NULL DEFAULT 1,
                `sticky_header` tinyint(1) NOT NULL DEFAULT 1,
                `header_style` varchar(255) NOT NULL DEFAULT 'default',
                `footer_about` text NULL,
                `footer_email` varchar(255) NULL,
                `footer_phone` varchar(255) NULL,
                `footer_address` varchar(255) NULL,
                `facebook_url` varchar(255) NULL,
                `instagram_url` varchar(255) NULL,
                `twitter_url` varchar(255) NULL,
                `youtube_url` varchar(255) NULL,
                `tiktok_url` varchar(255) NULL,
                `show_next_match` tinyint(1) NOT NULL DEFAULT 1,
                `show_last_results` tinyint(1) NOT NULL DEFAULT 1,
                `show_standings` tinyint(1) NOT NULL DEFAULT 1,
                `show_top_scorers` tinyint(1) NOT NULL DEFAULT 1,
                `show_news` tinyint(1) NOT NULL DEFAULT 1,
                `news_count` int NOT NULL DEFAULT 3,
                `enable_dark_mode` tinyint(1) NOT NULL DEFAULT 0,
                `enable_animations` tinyint(1) NOT NULL DEFAULT 1,
                `google_analytics_id` varchar(255) NULL,
                `created_at` timestamp NULL,
                `updated_at` timestamp NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");

        echo "✅ Tabelle erstellt\n\n";

        // Füge Standard-Eintrag ein
        DB::connection('tenant')->table('template_settings')->insert([
            'website_name' => 'NK Naprijed Cirkovljan',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "✅ Standard-Einstellungen eingefügt\n";

    } else {
        echo "✅ Tabelle 'template_settings' existiert bereits\n";

        $count = DB::connection('tenant')->table('template_settings')->count();
        echo "Anzahl Einträge: $count\n";
    }

} catch (Exception $e) {
    echo "❌ FEHLER: " . $e->getMessage() . "\n";
}
