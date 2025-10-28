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
CREATE TABLE `comet_matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `competition_fifa_id` int(11) NOT NULL,
  `age_category` varchar(100) NOT NULL,
  `age_category_name` varchar(100) NOT NULL,
  `international_competition_name` varchar(255) NOT NULL,
  `season` smallint(6) NOT NULL,
  `competition_status` varchar(50) NOT NULL,
  `match_fifa_id` int(11) NOT NULL,
  `match_status` varchar(50) DEFAULT NULL,
  `match_day` smallint(6) DEFAULT NULL,
  `match_place` varchar(255) DEFAULT NULL,
  `date_time_local` datetime DEFAULT NULL,
  `team_fifa_id_away` int(11) DEFAULT NULL,
  `team_name_away` varchar(255) DEFAULT NULL,
  `team_score_away` smallint(6) DEFAULT NULL,
  `team_logo_away` text DEFAULT NULL,
  `team_fifa_id_home` int(11) DEFAULT NULL,
  `team_name_home` varchar(255) DEFAULT NULL,
  `team_score_home` smallint(6) DEFAULT NULL,
  `team_logo_home` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comet_matches_simple_competition_fifa_id_index` (`competition_fifa_id`),
  KEY `comet_matches_simple_match_fifa_id_index` (`match_fifa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1800 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_matches`');
    }
};
