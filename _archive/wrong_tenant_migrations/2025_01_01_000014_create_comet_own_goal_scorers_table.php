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
CREATE TABLE `comet_own_goal_scorers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `competition_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_competitions',
  `player_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_players',
  `club_fifa_id` bigint(20) DEFAULT NULL COMMENT 'Club organisation FIFA ID',
  `team_fifa_id` bigint(20) DEFAULT NULL COMMENT 'Team FIFA ID',
  `international_first_name` varchar(255) DEFAULT NULL,
  `international_last_name` varchar(255) DEFAULT NULL,
  `popular_name` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `club_name` varchar(255) DEFAULT NULL,
  `team_name` varchar(255) DEFAULT NULL,
  `own_goals` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of own goals',
  `matches_played` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL COMMENT 'Position in own goal scorers list',
  `season` varchar(255) DEFAULT NULL COMMENT 'e.g., 2024/25',
  `nationality` varchar(255) DEFAULT NULL,
  `nationality_code` varchar(3) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `shirt_number` int(11) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `own_goal_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Details of each own goal' CHECK (json_valid(`own_goal_details`)),
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `competition_player_team_unique` (`competition_fifa_id`,`player_fifa_id`,`team_fifa_id`),
  KEY `comet_own_goal_scorers_competition_fifa_id_index` (`competition_fifa_id`),
  KEY `comet_own_goal_scorers_player_fifa_id_index` (`player_fifa_id`),
  KEY `comet_own_goal_scorers_club_fifa_id_index` (`club_fifa_id`),
  KEY `comet_own_goal_scorers_team_fifa_id_index` (`team_fifa_id`),
  KEY `comet_own_goal_scorers_competition_fifa_id_own_goals_index` (`competition_fifa_id`,`own_goals`),
  KEY `comet_own_goal_scorers_competition_fifa_id_rank_index` (`competition_fifa_id`,`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_own_goal_scorers`');
    }
};
