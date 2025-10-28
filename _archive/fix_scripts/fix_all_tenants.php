<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔍 CHECKING ALL TENANTS FOR MISSING TABLES\n";
echo "==========================================\n\n";

$tenants = Tenant::all();
$missingTables = ['groups', 'coach_group', 'club_players'];

echo "Total tenants: {$tenants->count()}\n";
echo "Checking for tables: " . implode(', ', $missingTables) . "\n\n";

$tenantsToFix = [];

foreach ($tenants as $tenant) {
    echo "Tenant: {$tenant->id} ({$tenant->name})\n";

    try {
        tenancy()->initialize($tenant);

        $missing = [];
        foreach ($missingTables as $table) {
            if (!Schema::hasTable($table)) {
                $missing[] = $table;
            }
        }

        if (count($missing) > 0) {
            echo "   ❌ Missing: " . implode(', ', $missing) . "\n";
            $tenantsToFix[] = [
                'tenant' => $tenant,
                'missing' => $missing
            ];
        } else {
            echo "   ✅ All tables present\n";
        }

        tenancy()->end();
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
        tenancy()->end();
    }
}

if (count($tenantsToFix) > 0) {
    echo "\n🔧 FIXING TENANTS WITH MISSING TABLES\n";
    echo "====================================\n\n";

    foreach ($tenantsToFix as $item) {
        $tenant = $item['tenant'];
        $missing = $item['missing'];

        echo "Fixing tenant: {$tenant->id}\n";

        tenancy()->initialize($tenant);

        foreach ($missing as $table) {
            try {
                switch ($table) {
                    case 'groups':
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
                        echo "   ✅ Created 'groups' table\n";
                        break;

                    case 'coach_group':
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
                        echo "   ✅ Created 'coach_group' table\n";
                        break;

                    case 'club_players':
                        DB::statement("
                            CREATE TABLE `club_players` (
                                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
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
                                PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                        ");
                        echo "   ✅ Created 'club_players' table\n";
                        break;
                }
            } catch (Exception $e) {
                echo "   ❌ Error creating '{$table}': " . $e->getMessage() . "\n";
            }
        }

        tenancy()->end();
        echo "\n";
    }

    echo "✅ ALL TENANTS FIXED!\n";
} else {
    echo "\n✅ ALL TENANTS HAVE ALL REQUIRED TABLES!\n";
}

echo "\n💡 RECOMMENDATION:\n";
echo "==================\n";
echo "To prevent this in the future:\n";
echo "1. Always run 'php artisan migrate' in central BEFORE creating new tenants\n";
echo "2. For existing tenants, run: php artisan tenants:migrate\n";
echo "3. Check TenancyServiceProvider pipeline is configured correctly\n";
