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
CREATE TABLE `comet_team_officials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_clubs_extended',
  `person_fifa_id` bigint(20) NOT NULL COMMENT 'Official person FIFA ID',
  `competition_fifa_id` bigint(20) DEFAULT NULL COMMENT 'FK to comet_competitions (if competition-specific)',
  `person_name` varchar(255) DEFAULT NULL,
  `local_person_name` varchar(255) DEFAULT NULL,
  `international_first_name` varchar(255) DEFAULT NULL,
  `international_last_name` varchar(255) DEFAULT NULL,
  `role` enum('COACH','ASSISTANT_COACH','GOALKEEPER_COACH','PHYSICAL_TRAINER','TEAM_DOCTOR','PHYSIOTHERAPIST','DIRECTOR','TECHNICAL_DIRECTOR','MANAGER','SPORTS_DIRECTOR','SCOUT','ANALYST','OTHER') NOT NULL COMMENT 'Official role in team',
  `role_description` varchar(255) DEFAULT NULL,
  `comet_role_name` varchar(255) DEFAULT NULL COMMENT 'COMET label key',
  `comet_role_name_key` varchar(255) DEFAULT NULL COMMENT 'Translation key',
  `status` enum('ACTIVE','INACTIVE','SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `valid_from` date DEFAULT NULL COMMENT 'Contract start date',
  `valid_to` date DEFAULT NULL COMMENT 'Contract end date',
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `nationality_code` varchar(3) DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `license_type` varchar(255) DEFAULT NULL COMMENT 'Trainer license (e.g., UEFA Pro)',
  `license_number` varchar(255) DEFAULT NULL,
  `license_valid_until` date DEFAULT NULL,
  `additional_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Extra official data' CHECK (json_valid(`additional_info`)),
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_official_unique` (`team_fifa_id`,`person_fifa_id`,`role`),
  KEY `comet_team_officials_team_fifa_id_index` (`team_fifa_id`),
  KEY `comet_team_officials_person_fifa_id_index` (`person_fifa_id`),
  KEY `comet_team_officials_competition_fifa_id_index` (`competition_fifa_id`),
  KEY `comet_team_officials_status_index` (`status`),
  KEY `comet_team_officials_team_fifa_id_status_index` (`team_fifa_id`,`status`),
  KEY `comet_team_officials_team_fifa_id_role_index` (`team_fifa_id`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_team_officials`');
    }
};
