<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
$domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', 'nknapijed.localhost')->first();
if ($domain) {
    tenancy()->initialize($domain->tenant);
}

echo "Checking for groups and player_group tables:\n";
echo "=============================================\n\n";

$tables = DB::select('SHOW TABLES');
$tableNames = array_map(function($table) {
    return array_values((array)$table)[0];
}, $tables);

$hasGroups = in_array('groups', $tableNames);
$hasPlayerGroup = in_array('player_group', $tableNames);

echo "groups table exists: " . ($hasGroups ? 'YES' : 'NO') . "\n";
echo "player_group table exists: " . ($hasPlayerGroup ? 'YES' : 'NO') . "\n\n";

if ($hasGroups) {
    echo "Structure of groups table:\n";
    $columns = DB::select('DESCRIBE groups');
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
}

if (!$hasPlayerGroup) {
    echo "\nCreating player_group pivot table...\n";

    DB::statement("
        CREATE TABLE `player_group` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `player_id` bigint(20) unsigned NOT NULL,
            `group_id` bigint(20) unsigned NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `player_group_player_id_foreign` (`player_id`),
            KEY `player_group_group_id_foreign` (`group_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✓ player_group table created successfully!\n";
}
