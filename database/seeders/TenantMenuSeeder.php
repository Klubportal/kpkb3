<?php

namespace Database\Seeders;

use App\Models\Tenant\TenantMenuItem;
use Illuminate\Database\Seeder;

class TenantMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Lösche bestehende Menüpunkte
        TenantMenuItem::query()->delete();

        $menuItems = [
            // Dashboard
            [
                'label' => 'Dashboard',
                'icon' => 'heroicon-o-home',
                'route' => 'filament.club.pages.dashboard',
                'sort_order' => 1,
                'is_active' => true,
                'group' => null,
            ],

            // Content Gruppe
            [
                'label' => 'News',
                'icon' => 'heroicon-o-newspaper',
                'url' => '/club/news',
                'sort_order' => 10,
                'is_active' => true,
                'group' => 'Content',
            ],

            // Verein Gruppe
            [
                'label' => 'Spieler',
                'icon' => 'heroicon-o-users',
                'url' => '/club/players',
                'sort_order' => 20,
                'is_active' => true,
                'group' => 'Verein',
                'permissions' => ['view_players'],
            ],
            [
                'label' => 'Mannschaften',
                'icon' => 'heroicon-o-user-group',
                'url' => '/club/teams',
                'sort_order' => 21,
                'is_active' => true,
                'group' => 'Verein',
            ],
            [
                'label' => 'Mitglieder',
                'icon' => 'heroicon-o-identification',
                'url' => '/club/members',
                'sort_order' => 22,
                'is_active' => true,
                'group' => 'Verein',
            ],

            // Spielbetrieb Gruppe
            [
                'label' => 'Spiele',
                'icon' => 'heroicon-o-trophy',
                'url' => '/club/matches',
                'sort_order' => 30,
                'is_active' => true,
                'group' => 'Spielbetrieb',
            ],
            [
                'label' => 'Training',
                'icon' => 'heroicon-o-academic-cap',
                'url' => '/club/trainings',
                'sort_order' => 31,
                'is_active' => true,
                'group' => 'Spielbetrieb',
            ],
            [
                'label' => 'Events',
                'icon' => 'heroicon-o-calendar',
                'url' => '/club/events',
                'sort_order' => 32,
                'is_active' => true,
                'group' => 'Spielbetrieb',
            ],

            // Verwaltung
            [
                'label' => 'Menü verwalten',
                'icon' => 'heroicon-o-bars-3',
                'url' => '/club/tenant-menu-items',
                'sort_order' => 90,
                'is_active' => true,
                'group' => 'Verwaltung',
                'permissions' => ['manage_menu'],
            ],
            [
                'label' => 'Einstellungen',
                'icon' => 'heroicon-o-cog-6-tooth',
                'url' => '/club/settings',
                'sort_order' => 99,
                'is_active' => true,
                'group' => null,
            ],
        ];

        foreach ($menuItems as $item) {
            TenantMenuItem::create($item);
        }

        $this->command->info('✓ ' . count($menuItems) . ' Menüpunkte erstellt');
    }
}
