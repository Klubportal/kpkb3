<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * Zentrale Menü-Struktur für alle Vereine/Tenants
     * Diese Struktur bleibt einheitlich, nur das Template/Styling ändert sich
     */
    public static function getMainMenu(): array
    {
        return [
            [
                'label' => 'Startseite',
                'url' => '/',
                'icon' => 'heroicon-o-home',
                'active' => 'home',
            ],
            [
                'label' => 'Mannschaften',
                'icon' => 'heroicon-o-user-group',
                'children' => [
                    [
                        'label' => 'Senioren',
                        'url' => '/teams/senioren',
                        'active' => 'teams.senioren',
                    ],
                    [
                        'label' => 'Junioren',
                        'url' => '/teams/junioren',
                        'active' => 'teams.junioren',
                    ],
                    [
                        'label' => 'Jugend',
                        'url' => '/teams/jugend',
                        'active' => 'teams.jugend',
                    ],
                ],
            ],
            [
                'label' => 'News',
                'url' => '/news',
                'icon' => 'heroicon-o-newspaper',
                'active' => 'news*',
            ],
            [
                'label' => 'Spielplan',
                'url' => '/spielplan',
                'icon' => 'heroicon-o-calendar',
                'active' => 'spielplan*',
            ],
            [
                'label' => 'Tabelle',
                'url' => '/tabelle',
                'icon' => 'heroicon-o-chart-bar',
                'active' => 'tabelle',
            ],
            [
                'label' => 'Galerie',
                'url' => '/galerie',
                'icon' => 'heroicon-o-photo',
                'active' => 'galerie*',
            ],
            [
                'label' => 'Verein',
                'icon' => 'heroicon-o-building-office',
                'children' => [
                    [
                        'label' => 'Über uns',
                        'url' => '/verein/ueber-uns',
                        'active' => 'verein.about',
                    ],
                    [
                        'label' => 'Vorstand',
                        'url' => '/verein/vorstand',
                        'active' => 'verein.board',
                    ],
                    [
                        'label' => 'Geschichte',
                        'url' => '/verein/geschichte',
                        'active' => 'verein.history',
                    ],
                    [
                        'label' => 'Mitglied werden',
                        'url' => '/verein/mitglied-werden',
                        'active' => 'verein.join',
                    ],
                ],
            ],
            [
                'label' => 'Sponsoren',
                'url' => '/sponsoren',
                'icon' => 'heroicon-o-star',
                'active' => 'sponsoren',
            ],
            [
                'label' => 'Kontakt',
                'url' => '/kontakt',
                'icon' => 'heroicon-o-envelope',
                'active' => 'kontakt',
            ],
        ];
    }

    /**
     * Cache-fähige Menü-Abrufung
     */
    public static function getCachedMenu(): array
    {
        return Cache::remember('main_menu', 3600, function () {
            return self::getMainMenu();
        });
    }

    /**
     * Prüft, ob ein Menüpunkt aktiv ist
     */
    public static function isActive(string $pattern): bool
    {
        return request()->is($pattern);
    }
}
