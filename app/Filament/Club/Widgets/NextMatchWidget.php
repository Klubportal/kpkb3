<?php

namespace App\Filament\Club\Widgets;

use App\Models\Comet\CometMatch;
use Filament\Widgets\Widget;

class NextMatchWidget extends Widget
{
    protected string $view = 'filament.club.widgets.next-match-widget';

    protected int | string | array $columnSpan = 1;

    public function getNextMatch()
    {
        $match = CometMatch::where('match_status', '!=', 'FINISHED')
            ->where('date_time_local', '>=', now())
            ->orderBy('date_time_local', 'asc')
            ->first();

        if (!$match) {
            return null;
        }

        return [
            'home_team' => $match->team_name_home,
            'away_team' => $match->team_name_away,
            'home_logo' => $match->team_logo_home,
            'away_logo' => $match->team_logo_away,
            'date' => $match->date_time_local?->format('d.m.Y'),
            'time' => $match->date_time_local?->format('H:i'),
            'location' => $match->match_place,
            'competition' => $match->international_competition_name,
        ];
    }
}
