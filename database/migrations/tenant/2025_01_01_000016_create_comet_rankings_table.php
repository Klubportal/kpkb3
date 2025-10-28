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
CREATE TABLE `comet_rankings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `competition_fifa_id` int(11) NOT NULL,
  `international_competition_name` varchar(255) NOT NULL,
  `age_category` varchar(100) NOT NULL,
  `age_category_name` varchar(100) NOT NULL,
  `position` smallint(6) DEFAULT NULL,
  `team_fifa_id` int(11) NOT NULL,
  `team_image_logo` text DEFAULT NULL,
  `international_team_name` varchar(255) NOT NULL,
  `matches_played` smallint(6) DEFAULT NULL,
  `wins` smallint(6) DEFAULT NULL,
  `draws` smallint(6) DEFAULT NULL,
  `losses` smallint(6) DEFAULT NULL,
  `goals_for` smallint(6) DEFAULT NULL,
  `goals_against` smallint(6) DEFAULT NULL,
  `goal_difference` smallint(6) DEFAULT NULL,
  `points` smallint(6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_competition_team` (`competition_fifa_id`,`team_fifa_id`),
  KEY `comet_rankings_competition_fifa_id_index` (`competition_fifa_id`),
  KEY `comet_rankings_team_fifa_id_index` (`team_fifa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_rankings`');
    }
};
