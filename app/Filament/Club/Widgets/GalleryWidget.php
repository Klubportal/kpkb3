<?php

namespace App\Filament\Club\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;

class GalleryWidget extends Widget
{
    protected string $view = 'filament.club.widgets.gallery-widget';

    protected int | string | array $columnSpan = 'full';

    public function getGalleryImages()
    {
        // Beispiel: Lade Bilder aus einem Gallery-Ordner oder aus Media-Library
        // Für Demo: Platzhalter-Bilder
        return [
            [
                'url' => 'https://via.placeholder.com/400x300/3b82f6/ffffff?text=Team+Foto+1',
                'title' => 'Mannschaftsfoto 2024/25',
                'date' => '15.09.2024',
            ],
            [
                'url' => 'https://via.placeholder.com/400x300/10b981/ffffff?text=Pokalsieger',
                'title' => 'Pokalsieger 2024',
                'date' => '20.08.2024',
            ],
            [
                'url' => 'https://via.placeholder.com/400x300/f59e0b/ffffff?text=Training',
                'title' => 'Trainingsauftakt',
                'date' => '01.08.2024',
            ],
            [
                'url' => 'https://via.placeholder.com/400x300/ef4444/ffffff?text=Derby',
                'title' => 'Derby-Sieg',
                'date' => '10.10.2024',
            ],
        ];
    }
}
