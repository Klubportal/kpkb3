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
CREATE TABLE `groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `label` varchar(191) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `user_custom_group` tinyint(1) NOT NULL DEFAULT 0,
  `slug` varchar(191) NOT NULL,
  `gender` varchar(1) NOT NULL DEFAULT 'm',
  `age_category` varchar(50) DEFAULT NULL,
  `age_category_name` varchar(191) DEFAULT NULL,
  `comet_competition_type` varchar(50) DEFAULT NULL,
  `order` tinyint(4) DEFAULT NULL,
  `featured_image` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups_slug_unique` (`slug`),
  KEY `groups_active_index` (`active`),
  KEY `groups_published_index` (`published`),
  KEY `groups_age_category_index` (`age_category`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `groups`');
    }
};
