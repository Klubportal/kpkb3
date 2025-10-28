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
CREATE TABLE `tenants` (
  `id` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT 'kp',
  `club_fifa_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `plan_id` bigint(20) unsigned DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `custom_domain` varchar(255) DEFAULT NULL,
  `custom_domain_verified` tinyint(1) NOT NULL DEFAULT 0,
  `custom_domain_verification_token` varchar(255) DEFAULT NULL,
  `custom_domain_verified_at` timestamp NULL DEFAULT NULL,
  `custom_domain_status` enum('pending','verifying','active','failed') NOT NULL DEFAULT 'pending',
  `custom_domain_dns_instructions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT 'Deutschland',
  `website` varchar(255) DEFAULT NULL,
  `subscription_start` date DEFAULT NULL,
  `subscription_end` date DEFAULT NULL,
  `comet_api_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`comet_api_data`)),
  `phone` varchar(255) DEFAULT NULL,
  `plan` varchar(255) NOT NULL DEFAULT 'trial',
  PRIMARY KEY (`id`),
  KEY `tenants_plan_id_foreign` (`plan_id`),
  CONSTRAINT `tenants_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `tenants`');
    }
};
