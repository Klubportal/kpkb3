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
CREATE TABLE `comet_match_team_officials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_matches',
  `team_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_clubs_extended',
  `person_fifa_id` bigint(20) NOT NULL COMMENT 'Official person FIFA ID',
  `person_name` varchar(255) DEFAULT NULL,
  `local_person_name` varchar(255) DEFAULT NULL,
  `team_nature` enum('HOME','AWAY') NOT NULL COMMENT 'Home or Away team',
  `role` enum('COACH','ASSISTANT_COACH','GOALKEEPER_COACH','PHYSICAL_TRAINER','TEAM_DOCTOR','PHYSIOTHERAPIST','DIRECTOR','TECHNICAL_DIRECTOR','MANAGER','OTHER') NOT NULL COMMENT 'Official role in team',
  `role_description` varchar(255) DEFAULT NULL,
  `comet_role_name` varchar(255) DEFAULT NULL COMMENT 'COMET label key',
  `comet_role_name_key` varchar(255) DEFAULT NULL COMMENT 'Translation key',
  `yellow_cards` int(11) NOT NULL DEFAULT 0,
  `red_cards` int(11) NOT NULL DEFAULT 0,
  `additional_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Extra official data' CHECK (json_valid(`additional_info`)),
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `match_team_official_unique` (`match_fifa_id`,`team_fifa_id`,`person_fifa_id`),
  KEY `comet_match_team_officials_match_fifa_id_index` (`match_fifa_id`),
  KEY `comet_match_team_officials_team_fifa_id_index` (`team_fifa_id`),
  KEY `comet_match_team_officials_person_fifa_id_index` (`person_fifa_id`),
  KEY `comet_match_team_officials_match_fifa_id_team_fifa_id_index` (`match_fifa_id`,`team_fifa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1412 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_match_team_officials`');
    }
};
