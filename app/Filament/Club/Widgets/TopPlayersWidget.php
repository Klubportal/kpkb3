<?php

namespace App\Filament\Club\Widgets;

use App\Models\Comet\CometPlayer;
use Filament\Widgets\Widget;

class TopPlayersWidget extends Widget
{
    protected string $view = 'filament.club.widgets.top-players-widget';

    protected int | string | array $columnSpan = 1;

    public function getPlayers()
    {
        return CometPlayer::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($player) {
                return [
                    'name' => $player->internationalFirstName . ' ' . $player->internationalLastName,
                    'position' => $player->position ?? 'N/A',
                    'number' => $player->shirtNumber ?? '-',
                    'image' => $player->pictureUrl,
                ];
            });
    }
}
