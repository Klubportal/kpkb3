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
CREATE TABLE `comet_player_competition_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) unsigned NOT NULL,
  `competition_id` bigint(20) unsigned NOT NULL,
  `matches` int(11) NOT NULL DEFAULT 0,
  `goals` int(11) NOT NULL DEFAULT 0,
  `assists` int(11) NOT NULL DEFAULT 0,
  `yellow_cards` int(11) NOT NULL DEFAULT 0,
  `red_cards` int(11) NOT NULL DEFAULT 0,
  `average_rating` decimal(3,2) DEFAULT NULL,
  `detailed_stats` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional stats like shots, passes, etc.' CHECK (json_valid(`detailed_stats`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `comet_player_competition_stats_player_id_competition_id_unique` (`player_id`,`competition_id`),
  KEY `comet_player_competition_stats_goals_index` (`goals`),
  KEY `comet_player_competition_stats_competition_id_goals_index` (`competition_id`,`goals`),
  CONSTRAINT `comet_player_competition_stats_player_id_foreign` FOREIGN KEY (`player_id`) REFERENCES `comet_players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_player_competition_stats`');
    }
};
