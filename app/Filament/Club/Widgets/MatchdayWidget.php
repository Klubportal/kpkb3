<?php

namespace App\Filament\Club\Widgets;

use App\Models\Comet\CometMatch;
use Filament\Widgets\Widget;

class MatchdayWidget extends Widget
{
    protected string $view = 'filament.club.widgets.matchday-widget';

    protected int | string | array $columnSpan = 'full';

    public function getCurrentMatchday()
    {
        // Finde aktuellen Spieltag (z.B. letzte 3 Tage + nächste 3 Tage)
        $matches = CometMatch::whereBetween('date_time_local', [
            now()->subDays(3),
            now()->addDays(3)
        ])
        ->orderBy('date_time_local', 'asc')
        ->limit(6)
        ->get()
        ->map(function ($match) {
            return [
                'home_team' => $match->team_name_home,
                'away_team' => $match->team_name_away,
                'home_logo' => $match->team_logo_home,
                'away_logo' => $match->team_logo_away,
                'home_score' => $match->team_score_home,
                'away_score' => $match->team_score_away,
                'status' => $match->match_status,
                'date' => $match->date_time_local?->format('d.m.Y'),
                'time' => $match->date_time_local?->format('H:i'),
            ];
        });

        return $matches;
    }
}
