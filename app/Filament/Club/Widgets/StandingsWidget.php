<?php

namespace App\Filament\Club\Widgets;

use App\Models\Comet\CometRanking;
use Filament\Widgets\Widget;

class StandingsWidget extends Widget
{
    protected string $view = 'filament.club.widgets.standings-widget';

    protected int | string | array $columnSpan = 1;

    public function getStandings()
    {
        return CometRanking::orderBy('position', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($ranking) {
                return [
                    'position' => $ranking->position,
                    'team' => $ranking->international_team_name,
                    'logo' => $ranking->team_image_logo,
                    'played' => $ranking->matches_played,
                    'won' => $ranking->wins,
                    'drawn' => $ranking->draws,
                    'lost' => $ranking->losses,
                    'goals_for' => $ranking->goals_for,
                    'goals_against' => $ranking->goals_against,
                    'goal_diff' => $ranking->goal_difference,
                    'points' => $ranking->points,
                ];
            });
    }
}
