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
CREATE TABLE `comet_match_players` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_matches',
  `team_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_clubs_extended',
  `person_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_players',
  `person_name` varchar(255) DEFAULT NULL,
  `local_person_name` varchar(255) DEFAULT NULL,
  `shirt_number` int(11) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL COMMENT 'Player position for this match',
  `team_nature` enum('HOME','AWAY') NOT NULL COMMENT 'Home or Away team',
  `captain` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is team captain',
  `goalkeeper` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is goalkeeper',
  `starting_lineup` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'In starting 11',
  `played` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Entered the match',
  `goals` int(11) NOT NULL DEFAULT 0 COMMENT 'Goals scored in this match',
  `assists` int(11) NOT NULL DEFAULT 0 COMMENT 'Assists in this match',
  `yellow_cards` int(11) NOT NULL DEFAULT 0 COMMENT 'Yellow cards received',
  `red_cards` int(11) NOT NULL DEFAULT 0 COMMENT 'Red cards received',
  `minutes_played` int(11) DEFAULT NULL COMMENT 'Minutes played',
  `substituted_in_minute` int(11) DEFAULT NULL COMMENT 'When substituted in',
  `substituted_out_minute` int(11) DEFAULT NULL COMMENT 'When substituted out',
  `substituted_by_player_fifa_id` bigint(20) DEFAULT NULL COMMENT 'Who replaced this player',
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `match_player_unique` (`match_fifa_id`,`person_fifa_id`),
  KEY `comet_match_players_match_fifa_id_index` (`match_fifa_id`),
  KEY `comet_match_players_team_fifa_id_index` (`team_fifa_id`),
  KEY `comet_match_players_person_fifa_id_index` (`person_fifa_id`),
  KEY `comet_match_players_match_fifa_id_team_fifa_id_index` (`match_fifa_id`,`team_fifa_id`),
  KEY `comet_match_players_match_fifa_id_starting_lineup_index` (`match_fifa_id`,`starting_lineup`)
) ENGINE=InnoDB AUTO_INCREMENT=618 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_match_players`');
    }
};
