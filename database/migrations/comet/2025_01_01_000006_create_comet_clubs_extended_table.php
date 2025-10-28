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
CREATE TABLE `comet_clubs_extended` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `club_fifa_id` bigint(20) NOT NULL COMMENT 'FIFA Club ID',
  `organisation_fifa_id` bigint(20) DEFAULT NULL COMMENT 'Parent organisation FIFA ID',
  `comet_id` bigint(20) DEFAULT NULL COMMENT 'Comet API Club ID',
  `fifa_id` bigint(20) NOT NULL COMMENT 'FIFA Official ID',
  `name` varchar(255) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL COMMENT 'Short club code (e.g., FCB)',
  `founded_year` int(11) DEFAULT NULL,
  `stadium_name` varchar(255) DEFAULT NULL,
  `facility_fifa_id` bigint(20) DEFAULT NULL COMMENT 'Main stadium/facility FIFA ID',
  `stadium_capacity` int(11) DEFAULT NULL,
  `coach_name` varchar(255) DEFAULT NULL,
  `coach_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`coach_info`)),
  `country` varchar(3) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `league_name` varchar(255) DEFAULT NULL,
  `club_info` text DEFAULT NULL,
  `colors` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `is_synced` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE' COMMENT 'ACTIVE, INACTIVE',
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `sync_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sync_metadata`)),
  `local_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Localized club names' CHECK (json_valid(`local_names`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `comet_clubs_extended_club_fifa_id_unique` (`club_fifa_id`),
  UNIQUE KEY `comet_clubs_extended_fifa_id_unique` (`fifa_id`),
  UNIQUE KEY `comet_clubs_extended_comet_id_unique` (`comet_id`),
  KEY `comet_clubs_extended_club_fifa_id_index` (`club_fifa_id`),
  KEY `comet_clubs_extended_country_index` (`country`),
  KEY `comet_clubs_extended_is_synced_index` (`is_synced`),
  KEY `comet_clubs_extended_status_index` (`status`),
  KEY `comet_clubs_extended_organisation_fifa_id_index` (`organisation_fifa_id`),
  KEY `comet_clubs_extended_facility_fifa_id_index` (`facility_fifa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_clubs_extended`');
    }
};
