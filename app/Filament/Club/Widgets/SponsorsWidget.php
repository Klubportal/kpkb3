<?php

namespace App\Filament\Club\Widgets;

use Filament\Widgets\Widget;

class SponsorsWidget extends Widget
{
    protected string $view = 'filament.club.widgets.sponsors-widget';

    protected int | string | array $columnSpan = 1;

    public function getSponsors()
    {
        // Beispiel: Lade Sponsoren aus Datenbank oder Settings
        return [
            [
                'name' => 'Hauptsponsor AG',
                'logo' => 'https://via.placeholder.com/200x80/1f2937/ffffff?text=Sponsor+1',
                'tier' => 'platinum',
                'website' => 'https://example.com',
            ],
            [
                'name' => 'Local Business GmbH',
                'logo' => 'https://via.placeholder.com/200x80/374151/ffffff?text=Sponsor+2',
                'tier' => 'gold',
                'website' => 'https://example.com',
            ],
            [
                'name' => 'Sport Equipment',
                'logo' => 'https://via.placeholder.com/200x80/4b5563/ffffff?text=Sponsor+3',
                'tier' => 'silver',
                'website' => 'https://example.com',
            ],
            [
                'name' => 'Community Partner',
                'logo' => 'https://via.placeholder.com/200x80/6b7280/ffffff?text=Sponsor+4',
                'tier' => 'bronze',
                'website' => 'https://example.com',
            ],
        ];
    }
}
