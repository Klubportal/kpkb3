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
CREATE TABLE `player_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) unsigned NOT NULL COMMENT 'Reference to comet_players in landlord DB',
  `group_id` bigint(20) unsigned NOT NULL,
  `position` varchar(50) DEFAULT NULL COMMENT 'Goalkeeper, Defender, Midfielder, Forward',
  `jersey_number` tinyint(4) DEFAULT NULL COMMENT 'Trikotnummer',
  `is_captain` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','injured','suspended') NOT NULL DEFAULT 'active',
  `joined_at` date DEFAULT NULL COMMENT 'Wann der Mannschaft beigetreten',
  `left_at` date DEFAULT NULL COMMENT 'Wann die Mannschaft verlassen',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_group_player_id_group_id_unique` (`player_id`,`group_id`),
  KEY `player_group_player_id_index` (`player_id`),
  KEY `player_group_group_id_foreign` (`group_id`),
  KEY `player_group_status_index` (`status`),
  CONSTRAINT `player_group_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `player_group`');
    }
};
