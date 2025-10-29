<?php

namespace App\Filament\Club\Widgets;

use App\Models\Comet\CometTopScorer;
use Filament\Widgets\Widget;

class TopScorersWidget extends Widget
{
    protected string $view = 'filament.club.widgets.top-scorers-widget';

    protected int | string | array $columnSpan = 1;

    public function getTopScorers()
    {
        return CometTopScorer::orderBy('goals', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($scorer) {
                return [
                    'name' => $scorer->international_first_name . ' ' . $scorer->international_last_name,
                    'club' => $scorer->club,
                    'goals' => $scorer->goals,
                    'logo' => $scorer->team_logo,
                ];
            });
    }
}
