<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'tenant_nkprigorjem';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔧 Creating tables in tenant_nkprigorjem...\n";
echo "============================================================\n\n";

// 1. Create groups table
echo "📋 Creating 'groups' table...\n";

$sql_groups = "
CREATE TABLE IF NOT EXISTS `groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `label` varchar(191) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `user_custom_group` tinyint(1) NOT NULL DEFAULT 0,
  `slug` varchar(191) NOT NULL,
  `gender` varchar(1) NOT NULL DEFAULT 'm',
  `order` tinyint(4) DEFAULT NULL,
  `featured_image` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups_slug_unique` (`slug`),
  KEY `groups_active_index` (`active`),
  KEY `groups_published_index` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql_groups) === TRUE) {
    echo "   ✅ groups table created successfully\n\n";
} else {
    echo "   ❌ Error: " . $conn->error . "\n\n";
}

// 2. Create coach_group table
echo "📋 Creating 'coach_group' table...\n";

$sql_coach_group = "
CREATE TABLE IF NOT EXISTS `coach_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coach_id` bigint(20) unsigned NOT NULL COMMENT 'Reference to comet_coaches in landlord DB',
  `group_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coach_group_coach_id_index` (`coach_id`),
  KEY `coach_group_group_id_foreign` (`group_id`),
  UNIQUE KEY `coach_group_coach_id_group_id_unique` (`coach_id`, `group_id`),
  CONSTRAINT `coach_group_group_id_foreign`
    FOREIGN KEY (`group_id`)
    REFERENCES `groups` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql_coach_group) === TRUE) {
    echo "   ✅ coach_group table created successfully\n\n";
} else {
    echo "   ❌ Error: " . $conn->error . "\n\n";
}

// Verify tables
echo "🔍 Verifying created tables...\n";
echo "------------------------------------------------------------\n";

$result = $conn->query("SHOW TABLES LIKE 'groups'");
if ($result->num_rows > 0) {
    echo "✅ groups table exists\n";
}

$result = $conn->query("SHOW TABLES LIKE 'coach_group'");
if ($result->num_rows > 0) {
    echo "✅ coach_group table exists\n";
}

echo "\n============================================================\n";
echo "✅ Migration completed for tenant_nkprigorjem\n";

$conn->close();
