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
CREATE TABLE `club_players` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comet_player_id` bigint(20) unsigned DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `popular_name` varchar(255) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `jersey_number` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `parent1_name` varchar(255) DEFAULT NULL,
  `parent1_email` varchar(255) DEFAULT NULL,
  `parent1_phone` varchar(255) DEFAULT NULL,
  `parent1_mobile` varchar(255) DEFAULT NULL,
  `parent2_name` varchar(255) DEFAULT NULL,
  `parent2_email` varchar(255) DEFAULT NULL,
  `parent2_phone` varchar(255) DEFAULT NULL,
  `parent2_mobile` varchar(255) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(255) DEFAULT NULL,
  `emergency_contact_relation` varchar(255) DEFAULT NULL,
  `has_medical_clearance` tinyint(1) DEFAULT 0,
  `medical_clearance_date` date DEFAULT NULL,
  `medical_notes` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comet_player_id` (`comet_player_id`),
  CONSTRAINT `club_players_ibfk_1` FOREIGN KEY (`comet_player_id`) REFERENCES `comet_players` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `club_players`');
    }
};
