<?php

namespace App\Filament\Club\Widgets;

use Filament\Widgets\Widget;

class NestIntegrationWidget extends Widget
{
    protected string $view = 'filament.club.widgets.nest-integration-widget';

    protected int | string | array $columnSpan = 'full';

    public function getNestData()
    {
        // NEST = Next Event Statistics & Tracking
        // Hier können verschiedene Event-Daten integriert werden
        return [
            'next_event' => [
                'type' => 'Training',
                'date' => '30.10.2025',
                'time' => '18:00 Uhr',
                'location' => 'Sportplatz Hauptstraße',
                'participants' => 24,
            ],
            'upcoming_events' => [
                [
                    'title' => 'Jahreshauptversammlung',
                    'date' => '15.11.2025',
                    'type' => 'Versammlung',
                ],
                [
                    'title' => 'Weihnachtsfeier',
                    'date' => '20.12.2025',
                    'type' => 'Event',
                ],
                [
                    'title' => 'Wintertrainingslager',
                    'date' => '05.01.2026',
                    'type' => 'Training',
                ],
            ],
            'team_metrics' => [
                'training_attendance' => 87,
                'average_age' => 24.5,
                'active_members' => 32,
            ],
        ];
    }
}
