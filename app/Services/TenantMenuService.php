<?php

namespace App\Services;

use App\Models\Tenant\TenantMenuItem;
use Filament\Navigation\MenuItem;
use Illuminate\Support\Collection;

class TenantMenuService
{
    /**
     * Get all active menu items for current tenant
     */
    public function getMenuItems(?object $user = null): Collection
    {
        $user = $user ?? auth()->user();

        return TenantMenuItem::active()
            ->root()
            ->ordered()
            ->with('children')
            ->get()
            ->filter(fn($item) => $item->canView($user));
    }

    /**
     * Generate Filament MenuItem array for tenantMenuItems
     */
    public function getFilamentMenuItems(?object $user = null): array
    {
        return $this->getMenuItems($user)
            ->map(function (TenantMenuItem $item) {
                return $this->convertToFilamentMenuItem($item);
            })
            ->toArray();
    }

    /**
     * Convert TenantMenuItem to Filament MenuItem
     */
    protected function convertToFilamentMenuItem(TenantMenuItem $item): MenuItem
    {
        $menuItem = MenuItem::make()
            ->label($item->label);

        // Set icon
        if ($item->icon) {
            $menuItem->icon($item->icon);
        }

        // Set URL
        if ($url = $item->getUrl()) {
            $menuItem->url($url);
        }

        // Set badge
        if ($item->badge) {
            $menuItem->badge($item->badge);
            if ($item->badge_color) {
                $menuItem->badgeColor($item->badge_color);
            }
        }

        // Set sort order
        if ($item->sort_order) {
            $menuItem->sort($item->sort_order);
        }

        return $menuItem;
    }

    /**
     * Create default menu items for new tenant
     */
    public function createDefaultMenuItems(): void
    {
        $defaultItems = [
            [
                'label' => 'Dashboard',
                'icon' => 'heroicon-o-home',
                'route' => 'filament.club.pages.dashboard',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'label' => 'News',
                'icon' => 'heroicon-o-newspaper',
                'route' => 'filament.club.resources.news.index',
                'sort_order' => 10,
                'is_active' => true,
                'group' => 'Content',
            ],
            [
                'label' => 'Spieler',
                'icon' => 'heroicon-o-users',
                'route' => 'filament.club.resources.players.index',
                'sort_order' => 20,
                'is_active' => true,
                'group' => 'Verein',
            ],
            [
                'label' => 'Mannschaften',
                'icon' => 'heroicon-o-user-group',
                'route' => 'filament.club.resources.teams.index',
                'sort_order' => 21,
                'is_active' => true,
                'group' => 'Verein',
            ],
            [
                'label' => 'Spiele',
                'icon' => 'heroicon-o-trophy',
                'route' => 'filament.club.resources.matches.index',
                'sort_order' => 30,
                'is_active' => true,
                'group' => 'Spielbetrieb',
            ],
            [
                'label' => 'Training',
                'icon' => 'heroicon-o-academic-cap',
                'route' => 'filament.club.resources.trainings.index',
                'sort_order' => 31,
                'is_active' => true,
                'group' => 'Spielbetrieb',
            ],
            [
                'label' => 'Einstellungen',
                'icon' => 'heroicon-o-cog-6-tooth',
                'route' => 'filament.club.pages.manage-club-settings',
                'sort_order' => 99,
                'is_active' => true,
            ],
        ];

        foreach ($defaultItems as $itemData) {
            TenantMenuItem::create($itemData);
        }
    }

    /**
     * Sync menu items from array (for import/export)
     */
    public function syncMenuItems(array $items): void
    {
        foreach ($items as $itemData) {
            TenantMenuItem::updateOrCreate(
                ['label' => $itemData['label']],
                $itemData
            );
        }
    }
}
