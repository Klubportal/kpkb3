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
CREATE TABLE `comet_match_phases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_fifa_id` bigint(20) NOT NULL COMMENT 'FK to comet_matches',
  `phase` enum('FIRST_HALF','SECOND_HALF','FIRST_ET','SECOND_ET','PEN','BEFORE_THE_MATCH','DURING_THE_BREAK','AFTER_THE_MATCH','PER_1','PER_2','PER_3') NOT NULL COMMENT 'Match phase type',
  `home_score` int(11) DEFAULT NULL COMMENT 'Home team score in this phase',
  `away_score` int(11) DEFAULT NULL COMMENT 'Away team score in this phase',
  `regular_time` int(11) DEFAULT NULL COMMENT 'Regular time in minutes',
  `stoppage_time` int(11) DEFAULT NULL COMMENT 'Stoppage/injury time in minutes',
  `phase_length` int(11) DEFAULT NULL COMMENT 'Total phase length (regular + stoppage)',
  `start_date_time` datetime DEFAULT NULL COMMENT 'Phase start (local time)',
  `end_date_time` datetime DEFAULT NULL COMMENT 'Phase end (local time)',
  `start_date_time_utc` datetime DEFAULT NULL COMMENT 'Phase start (UTC)',
  `end_date_time_utc` datetime DEFAULT NULL COMMENT 'Phase end (UTC)',
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `match_phase_unique` (`match_fifa_id`,`phase`),
  KEY `comet_match_phases_match_fifa_id_index` (`match_fifa_id`),
  KEY `comet_match_phases_match_fifa_id_start_date_time_index` (`match_fifa_id`,`start_date_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1015 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_match_phases`');
    }
};
