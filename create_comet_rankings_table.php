<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

echo "Creating comet_rankings table for all tenants...\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "Processing tenant: {$tenant->id}\n";

    $tenant->run(function () use ($tenant) {
        try {
            // Check if table already exists
            $exists = DB::select("SHOW TABLES LIKE 'comet_rankings'");

            if (empty($exists)) {
                // Create the table
                DB::statement("
                    CREATE TABLE `comet_rankings` (
                        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        `competition_fifa_id` int(11) NOT NULL,
                        `team_fifa_id` int(11) NOT NULL,
                        `team_name` varchar(255) NOT NULL,
                        `team_short_club_name` varchar(100) DEFAULT NULL,
                        `position` int(11) NOT NULL,
                        `matches_played` int(11) DEFAULT 0,
                        `wins` int(11) DEFAULT 0,
                        `draws` int(11) DEFAULT 0,
                        `losses` int(11) DEFAULT 0,
                        `goals_for` int(11) DEFAULT 0,
                        `goals_against` int(11) DEFAULT 0,
                        `goal_difference` int(11) DEFAULT 0,
                        `points` int(11) DEFAULT 0,
                        `created_at` timestamp NULL DEFAULT NULL,
                        `updated_at` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `comet_rankings_competition_fifa_id_index` (`competition_fifa_id`),
                        KEY `comet_rankings_team_fifa_id_index` (`team_fifa_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "  ✓ Table comet_rankings created successfully\n";
            } else {
                echo "  - Table comet_rankings already exists\n";
            }
        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    });
}

echo "\n✓ Done!\n";
