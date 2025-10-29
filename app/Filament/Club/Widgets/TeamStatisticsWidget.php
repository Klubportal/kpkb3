<?php

namespace App\Filament\Club\Widgets;

use App\Models\Comet\CometMatch;
use App\Models\Comet\CometPlayer;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class TeamStatisticsWidget extends Widget
{
    protected string $view = 'filament.club.widgets.team-statistics-widget';

    protected int | string | array $columnSpan = 1;

    public function getStatistics()
    {
        $totalMatches = CometMatch::count();
        $wonMatches = CometMatch::where('match_status', 'FINISHED')
            ->whereColumn('team_score_home', '>', 'team_score_away')
            ->count();
        $drawnMatches = CometMatch::where('match_status', 'FINISHED')
            ->whereColumn('team_score_home', '=', 'team_score_away')
            ->count();
        $lostMatches = CometMatch::where('match_status', 'FINISHED')
            ->whereColumn('team_score_home', '<', 'team_score_away')
            ->count();

        $totalPlayers = CometPlayer::count();

        $totalGoalsScored = CometMatch::where('match_status', 'FINISHED')
            ->sum('team_score_home');

        $totalGoalsConceded = CometMatch::where('match_status', 'FINISHED')
            ->sum('team_score_away');

        return [
            'total_matches' => $totalMatches,
            'won' => $wonMatches,
            'drawn' => $drawnMatches,
            'lost' => $lostMatches,
            'win_rate' => $totalMatches > 0 ? round(($wonMatches / $totalMatches) * 100, 1) : 0,
            'total_players' => $totalPlayers,
            'goals_scored' => $totalGoalsScored,
            'goals_conceded' => $totalGoalsConceded,
            'goal_difference' => $totalGoalsScored - $totalGoalsConceded,
        ];
    }
}
