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
CREATE TABLE `sync_errors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sync_log_id` bigint(20) unsigned NOT NULL,
  `error_type` enum('api_error','validation_error','database_error','timeout_error','authentication_error') NOT NULL,
  `error_message` text NOT NULL,
  `error_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Stack trace, request details, etc.' CHECK (json_valid(`error_context`)),
  `comet_endpoint` varchar(255) DEFAULT NULL,
  `record_id` varchar(100) DEFAULT NULL COMMENT 'Match ID, Player ID, etc.',
  `http_status_code` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_errors_sync_log_id_foreign` (`sync_log_id`),
  KEY `sync_errors_error_type_index` (`error_type`),
  CONSTRAINT `sync_errors_sync_log_id_foreign` FOREIGN KEY (`sync_log_id`) REFERENCES `sync_logs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `sync_errors`');
    }
};
