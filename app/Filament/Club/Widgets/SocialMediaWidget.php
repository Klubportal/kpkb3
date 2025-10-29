<?php

namespace App\Filament\Club\Widgets;

use Filament\Widgets\Widget;

class SocialMediaWidget extends Widget
{
    protected string $view = 'filament.club.widgets.social-media-widget';

    protected int | string | array $columnSpan = 1;

    public function getSocialPosts()
    {
        // Beispiel: Integration mit Social Media APIs oder manuelle Einträge
        return [
            [
                'platform' => 'twitter',
                'icon' => '𝕏',
                'author' => '@UnserVerein',
                'content' => 'Großartiger Sieg heute! 💪⚽ #TeamSpirit',
                'time' => '2 Std.',
                'likes' => 142,
            ],
            [
                'platform' => 'instagram',
                'icon' => '📷',
                'author' => '@unser_verein',
                'content' => 'Trainingsimpressionen vom Wochenende 📸',
                'time' => '5 Std.',
                'likes' => 89,
            ],
            [
                'platform' => 'facebook',
                'icon' => '👍',
                'author' => 'Unser Verein',
                'content' => 'Nächstes Heimspiel: Samstag 15:00 Uhr! Kommt vorbei!',
                'time' => '1 Tag',
                'likes' => 234,
            ],
        ];
    }
}
