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
CREATE TABLE `comet_match_officials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_fifa_id` int(11) NOT NULL,
  `person_fifa_id` int(11) NOT NULL,
  `international_first_name` varchar(255) DEFAULT NULL,
  `international_last_name` varchar(255) DEFAULT NULL,
  `local_first_name` varchar(255) DEFAULT NULL,
  `local_last_name` varchar(255) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `role_description` varchar(255) DEFAULT NULL,
  `comet_role_name` varchar(255) DEFAULT NULL,
  `gender` enum('MALE','FEMALE','OTHER') DEFAULT 'MALE',
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(3) DEFAULT NULL,
  `nationality_fifa` varchar(3) DEFAULT NULL,
  `country_of_birth` varchar(3) DEFAULT NULL,
  `country_of_birth_fifa` varchar(3) DEFAULT NULL,
  `region_of_birth` varchar(255) DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `current_place` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_match_person_role` (`match_fifa_id`,`person_fifa_id`,`role`),
  KEY `idx_match` (`match_fifa_id`),
  KEY `idx_person` (`person_fifa_id`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=1188 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_match_officials`');
    }
};
