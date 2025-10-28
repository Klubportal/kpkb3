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
CREATE TABLE `comet_coaches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `club_fifa_id` bigint(20) NOT NULL,
  `person_fifa_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `popular_name` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `country_of_birth` varchar(3) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `nationality_code` varchar(3) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `role_description` varchar(255) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_synced` tinyint(1) DEFAULT 0,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `local_names` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_coach` (`club_fifa_id`,`person_fifa_id`),
  KEY `idx_club` (`club_fifa_id`),
  KEY `idx_person` (`person_fifa_id`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_coaches`');
    }
};
