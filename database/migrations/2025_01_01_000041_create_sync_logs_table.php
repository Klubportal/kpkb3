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
CREATE TABLE `sync_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(191) NOT NULL,
  `sync_type` varchar(50) NOT NULL COMMENT 'competitions, matches, rankings, match_events, match_officials, team_officials, representatives',
  `status` enum('success','failed','partial','running') NOT NULL DEFAULT 'running',
  `records_processed` int(11) NOT NULL DEFAULT 0,
  `records_inserted` int(11) NOT NULL DEFAULT 0,
  `records_updated` int(11) NOT NULL DEFAULT 0,
  `records_skipped` int(11) NOT NULL DEFAULT 0,
  `records_failed` int(11) NOT NULL DEFAULT 0,
  `total_records` int(11) NOT NULL DEFAULT 0,
  `started_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `error_details` text DEFAULT NULL,
  `sync_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Parameters like date_from, date_to' CHECK (json_valid(`sync_params`)),
  `sync_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sync_metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_logs_tenant_id_index` (`tenant_id`),
  KEY `sync_logs_sync_type_index` (`sync_type`),
  KEY `sync_logs_status_index` (`status`),
  KEY `sync_logs_started_at_index` (`started_at`)
) ENGINE=InnoDB AUTO_INCREMENT=974 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `sync_logs`');
    }
};
