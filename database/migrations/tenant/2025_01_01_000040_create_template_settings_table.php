<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
CREATE TABLE `template_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `website_name` varchar(255) DEFAULT 'NK Prigorjem',
  `slogan` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `logo_height` int(11) DEFAULT 50,
  `primary_color` varchar(20) DEFAULT '#DC052D',
  `secondary_color` varchar(20) DEFAULT '#0066B2',
  `accent_color` varchar(20) DEFAULT '#FCBF49',
  `header_bg_color` varchar(20) DEFAULT '#FFFFFF',
  `footer_bg_color` varchar(20) DEFAULT '#1A1A1A',
  `text_color` varchar(20) DEFAULT '#1f2937',
  `show_logo` tinyint(1) DEFAULT 1,
  `sticky_header` tinyint(1) DEFAULT 1,
  `header_style` varchar(20) DEFAULT 'default',
  `footer_about` text DEFAULT NULL,
  `footer_email` varchar(255) DEFAULT NULL,
  `footer_phone` varchar(255) DEFAULT NULL,
  `footer_address` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `tiktok_url` varchar(255) DEFAULT NULL,
  `show_next_match` tinyint(1) DEFAULT 1,
  `show_last_results` tinyint(1) DEFAULT 1,
  `show_standings` tinyint(1) DEFAULT 1,
  `show_top_scorers` tinyint(1) DEFAULT 1,
  `show_news` tinyint(1) DEFAULT 1,
  `news_count` int(11) DEFAULT 3,
  `enable_dark_mode` tinyint(1) DEFAULT 0,
  `enable_animations` tinyint(1) DEFAULT 1,
  `google_analytics_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `header_text_color` varchar(7) DEFAULT '#1F2937',
  `badge_bg_color` varchar(7) DEFAULT '#DC052D',
  `badge_text_color` varchar(7) DEFAULT '#FFFFFF',
  `footer_text_color` varchar(7) DEFAULT '#FFFFFF',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `template_settings`');
    }
};
