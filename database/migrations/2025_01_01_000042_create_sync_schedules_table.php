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
CREATE TABLE `sync_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(191) DEFAULT NULL COMMENT 'NULL = all tenants',
  `sync_type` varchar(50) NOT NULL,
  `frequency` enum('hourly','daily','weekly','monthly','custom') NOT NULL,
  `schedule_time` varchar(10) DEFAULT NULL COMMENT 'HH:MM format',
  `schedule_day` tinyint(4) DEFAULT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `schedule_cron` varchar(100) DEFAULT NULL COMMENT 'Custom cron expression',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sync_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional parameters like date ranges' CHECK (json_valid(`sync_params`)),
  `last_run_at` datetime DEFAULT NULL,
  `next_run_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_schedules_tenant_id_index` (`tenant_id`),
  KEY `sync_schedules_sync_type_index` (`sync_type`),
  KEY `sync_schedules_is_active_index` (`is_active`),
  KEY `sync_schedules_next_run_at_index` (`next_run_at`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `sync_schedules`');
    }
};
