<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔧 MANUALLY CREATING MISSING TABLES FOR TENANT\n";
echo "==============================================\n\n";

// Get the tenant
$tenant = Tenant::find('nknapijed');

if (!$tenant) {
    echo "❌ Tenant 'nknapijed' not found!\n";
    exit(1);
}

echo "Tenant: {$tenant->name} ({$tenant->id})\n";
echo "Database: tenant_{$tenant->id}\n\n";

// Initialize tenant context
tenancy()->initialize($tenant);

echo "📋 CREATING MISSING TABLES\n";
echo "==========================\n\n";

// Create groups table
echo "1. Creating 'groups' table...\n";
try {
    if (!Schema::hasTable('groups')) {
        DB::statement("
            CREATE TABLE `groups` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(191) NOT NULL,
                `label` varchar(191) NOT NULL,
                `active` tinyint(1) NOT NULL DEFAULT '1',
                `published` tinyint(1) NOT NULL DEFAULT '0',
                `user_custom_group` tinyint(1) NOT NULL DEFAULT '0',
                `slug` varchar(191) NOT NULL,
                `gender` char(1) NOT NULL DEFAULT 'm',
                `order` tinyint DEFAULT NULL,
                `featured_image` text,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `groups_slug_unique` (`slug`),
                KEY `groups_active_index` (`active`),
                KEY `groups_published_index` (`published`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "   ✅ 'groups' table created successfully\n";
    } else {
        echo "   ℹ️  'groups' table already exists\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error creating 'groups' table: " . $e->getMessage() . "\n";
}

// Create coach_group pivot table
echo "\n2. Creating 'coach_group' pivot table...\n";
try {
    if (!Schema::hasTable('coach_group')) {
        DB::statement("
            CREATE TABLE `coach_group` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `coach_id` bigint unsigned NOT NULL,
                `group_id` bigint unsigned NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `coach_group_coach_id_group_id_unique` (`coach_id`,`group_id`),
                KEY `coach_group_coach_id_index` (`coach_id`),
                KEY `coach_group_group_id_foreign` (`group_id`),
                CONSTRAINT `coach_group_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "   ✅ 'coach_group' table created successfully\n";
    } else {
        echo "   ℹ️  'coach_group' table already exists\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error creating 'coach_group' table: " . $e->getMessage() . "\n";
}

// Create club_players table if missing
echo "\n3. Creating 'club_players' table if missing...\n";
try {
    if (!Schema::hasTable('club_players')) {
        DB::statement("
            CREATE TABLE `club_players` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `comet_player_id` bigint unsigned DEFAULT NULL,
                `first_name` varchar(255) NOT NULL,
                `last_name` varchar(255) NOT NULL,
                `popular_name` varchar(255) DEFAULT NULL,
                `date_of_birth` date NOT NULL,
                `nationality` varchar(255) DEFAULT NULL,
                `photo_url` varchar(255) DEFAULT NULL,
                `position` varchar(255) DEFAULT NULL,
                `jersey_number` int DEFAULT NULL,
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
                `has_medical_clearance` tinyint(1) NOT NULL DEFAULT '0',
                `medical_clearance_date` date DEFAULT NULL,
                `medical_notes` text,
                `allergies` text,
                `notes` text,
                `active` tinyint(1) NOT NULL DEFAULT '1',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `club_players_comet_player_id_foreign` (`comet_player_id`),
                CONSTRAINT `club_players_comet_player_id_foreign` FOREIGN KEY (`comet_player_id`) REFERENCES `comet_players` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "   ✅ 'club_players' table created successfully\n";
    } else {
        echo "   ℹ️  'club_players' table already exists\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error creating 'club_players' table: " . $e->getMessage() . "\n";
}

// Verify all tables now exist
echo "\n4. Verifying tables...\n";
$requiredTables = ['groups', 'coach_group', 'club_players'];
foreach ($requiredTables as $table) {
    $exists = Schema::hasTable($table);
    echo "   " . ($exists ? "✅" : "❌") . " {$table}\n";
}

tenancy()->end();

echo "\n✅ TABLES CREATION COMPLETED!\n";
echo "\nYou should now be able to access:\n";
echo "http://nknapijed.localhost:8000/club/comet-players\n";
