<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kpkb3';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔧 Creating Sync Monitoring System in landlord DB...\n";
echo "============================================================\n\n";

// 1. sync_logs table
echo "📋 Creating sync_logs table...\n";
$sql1 = "
CREATE TABLE IF NOT EXISTS `sync_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(191) NOT NULL,
  `sync_type` varchar(50) NOT NULL COMMENT 'competitions, matches, rankings, match_events, match_officials, team_officials, representatives',
  `status` enum('success','failed','partial','running') NOT NULL DEFAULT 'running',
  `records_processed` int(11) NOT NULL DEFAULT 0,
  `records_inserted` int(11) NOT NULL DEFAULT 0,
  `records_updated` int(11) NOT NULL DEFAULT 0,
  `records_failed` int(11) NOT NULL DEFAULT 0,
  `started_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sync_params` json DEFAULT NULL COMMENT 'Parameters like date_from, date_to',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_logs_tenant_id_index` (`tenant_id`),
  KEY `sync_logs_sync_type_index` (`sync_type`),
  KEY `sync_logs_status_index` (`status`),
  KEY `sync_logs_started_at_index` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql1) === TRUE) {
    echo "✅ sync_logs created\n";
} else {
    echo "⚠️  sync_logs: " . $conn->error . "\n";
}

// 2. sync_errors table
echo "📋 Creating sync_errors table...\n";
$sql2 = "
CREATE TABLE IF NOT EXISTS `sync_errors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sync_log_id` bigint(20) unsigned NOT NULL,
  `error_type` enum('api_error','validation_error','database_error','timeout_error','authentication_error') NOT NULL,
  `error_message` text NOT NULL,
  `error_context` json DEFAULT NULL COMMENT 'Stack trace, request details, etc.',
  `comet_endpoint` varchar(255) DEFAULT NULL,
  `record_id` varchar(100) DEFAULT NULL COMMENT 'Match ID, Player ID, etc.',
  `http_status_code` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_errors_sync_log_id_foreign` (`sync_log_id`),
  KEY `sync_errors_error_type_index` (`error_type`),
  CONSTRAINT `sync_errors_sync_log_id_foreign`
    FOREIGN KEY (`sync_log_id`)
    REFERENCES `sync_logs` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql2) === TRUE) {
    echo "✅ sync_errors created\n";
} else {
    echo "⚠️  sync_errors: " . $conn->error . "\n";
}

// 3. sync_schedules table
echo "📋 Creating sync_schedules table...\n";
$sql3 = "
CREATE TABLE IF NOT EXISTS `sync_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(191) DEFAULT NULL COMMENT 'NULL = all tenants',
  `sync_type` varchar(50) NOT NULL,
  `frequency` enum('hourly','daily','weekly','monthly','custom') NOT NULL,
  `schedule_time` varchar(10) DEFAULT NULL COMMENT 'HH:MM format',
  `schedule_day` tinyint(4) DEFAULT NULL COMMENT '0=Sunday, 1=Monday, etc.',
  `schedule_cron` varchar(100) DEFAULT NULL COMMENT 'Custom cron expression',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sync_params` json DEFAULT NULL COMMENT 'Additional parameters like date ranges',
  `last_run_at` datetime DEFAULT NULL,
  `next_run_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_schedules_tenant_id_index` (`tenant_id`),
  KEY `sync_schedules_sync_type_index` (`sync_type`),
  KEY `sync_schedules_is_active_index` (`is_active`),
  KEY `sync_schedules_next_run_at_index` (`next_run_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql3) === TRUE) {
    echo "✅ sync_schedules created\n\n";
} else {
    echo "⚠️  sync_schedules: " . $conn->error . "\n\n";
}

echo "============================================================\n";
echo "✅ SCHRITT 2 - Tabellen erstellt!\n\n";

echo "📋 Verifying tables...\n";
echo "------------------------------------------------------------\n";
$tables = ['sync_logs', 'sync_errors', 'sync_schedules'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ $table\n";
    } else {
        echo "❌ $table - NOT FOUND\n";
    }
}

$conn->close();
