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
CREATE TABLE `comet_club_competitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `competitionFifaId` int(11) DEFAULT NULL,
  `ageCategory` varchar(100) NOT NULL,
  `ageCategoryName` varchar(100) NOT NULL,
  `internationalName` varchar(255) NOT NULL,
  `season` smallint(6) NOT NULL,
  `status` varchar(50) NOT NULL,
  `flag_played_matches` int(11) DEFAULT NULL,
  `flag_scheduled_matches` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comet_club_competitions_competitionfifaid_index` (`competitionFifaId`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `comet_club_competitions`');
    }
};
