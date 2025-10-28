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
CREATE TABLE `comet_top_scorers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `competition_fifa_id` int(11) NOT NULL,
  `international_competition_name` varchar(255) NOT NULL,
  `age_category` varchar(100) NOT NULL,
  `age_category_name` varchar(100) NOT NULL,
  `player_fifa_id` int(11) DEFAULT NULL,
  `goals` smallint(6) DEFAULT NULL,
  `international_first_name` varchar(255) NOT NULL,
  `international_last_name` varchar(255) NOT NULL,
  `club` varchar(255) DEFAULT NULL,
  `club_id` int(11) NOT NULL,
  `team_logo` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_competition_player` (`competition_fifa_id`,`player_fifa_id`),
  KEY `comet_top_scorers_competition_fifa_id_index` (`competition_fifa_id`),
  KEY `comet_top_scorers_player_fifa_id_index` (`player_fifa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=802 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_top_scorers`');
    }
};
