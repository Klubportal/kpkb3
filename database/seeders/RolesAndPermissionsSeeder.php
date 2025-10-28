<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // Player Management
            'view_players',
            'create_players',
            'edit_players',
            'delete_players',

            // Team Management
            'view_teams',
            'create_teams',
            'edit_teams',
            'delete_teams',

            // Game Management
            'view_games',
            'create_games',
            'edit_games',
            'delete_games',
            'manage_live_scores',

            // News Management
            'view_news',
            'create_news',
            'edit_news',
            'delete_news',
            'publish_news',

            // Member Management
            'view_members',
            'create_members',
            'edit_members',
            'delete_members',

            // Settings
            'manage_settings',
            'manage_billing',

            // Statistics
            'view_statistics',
            'export_data',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and assign permissions

        // Super Admin (Platform Owner)
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Club Admin
        $clubAdmin = Role::create(['name' => 'club_admin']);
        $clubAdmin->givePermissionTo(Permission::all());

        // Official/Board Member
        $official = Role::create(['name' => 'official']);
        $official->givePermissionTo([
            'view_users', 'view_players', 'edit_players', 'view_teams',
            'view_games', 'view_news', 'view_members', 'view_statistics'
        ]);

        // Trainer/Coach
        $trainer = Role::create(['name' => 'trainer']);
        $trainer->givePermissionTo([
            'view_players', 'edit_players', 'view_teams', 'edit_teams',
            'view_games', 'create_news', 'edit_news', 'view_statistics'
        ]);

        // Player
        $player = Role::create(['name' => 'player']);
        $player->givePermissionTo(['view_games', 'view_news', 'view_statistics']);

        // Parent
        $parent = Role::create(['name' => 'parent']);
        $parent->givePermissionTo(['view_games', 'view_news']);

        // Member
        $member = Role::create(['name' => 'member']);
        $member->givePermissionTo(['view_news', 'view_games']);

        // Fan/Supporter
        $fan = Role::create(['name' => 'fan']);
        $fan->givePermissionTo(['view_news', 'view_games']);
    }
}
