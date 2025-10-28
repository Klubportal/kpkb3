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
CREATE TABLE `comet_match_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_event_fifa_id` bigint(20) NOT NULL COMMENT 'Unique event ID from API',
  `match_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_matches',
  `competition_fifa_id` bigint(20) NOT NULL COMMENT 'Competition ID',
  `player_fifa_id` bigint(20) DEFAULT NULL COMMENT 'Primary player involved',
  `player_name` varchar(255) DEFAULT NULL,
  `shirt_number` int(11) DEFAULT NULL,
  `player_fifa_id_2` bigint(20) DEFAULT NULL COMMENT 'Secondary player (e.g., for assist/substitution)',
  `player_name_2` varchar(255) DEFAULT NULL,
  `team_fifa_id` bigint(20) DEFAULT NULL COMMENT 'Team ID',
  `match_team` enum('HOME','AWAY') NOT NULL,
  `event_type` enum('goal','penalty_goal','own_goal','yellow_card','red_card','yellow_red_card','substitution','penalty_missed') NOT NULL,
  `event_minute` int(11) NOT NULL COMMENT 'Minute when event occurred',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `comet_match_events_match_event_fifa_id_unique` (`match_event_fifa_id`),
  KEY `comet_match_events_match_fifa_id_index` (`match_fifa_id`),
  KEY `comet_match_events_competition_fifa_id_index` (`competition_fifa_id`),
  KEY `comet_match_events_player_fifa_id_index` (`player_fifa_id`),
  KEY `comet_match_events_team_fifa_id_index` (`team_fifa_id`),
  KEY `comet_match_events_event_type_index` (`event_type`),
  KEY `comet_match_events_match_fifa_id_event_minute_index` (`match_fifa_id`,`event_minute`)
) ENGINE=InnoDB AUTO_INCREMENT=3500 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_match_events`');
    }
};
