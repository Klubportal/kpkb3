<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant\TemplateSetting;

class HomeController extends Controller
{
    public function index()
    {
        $settings = TemplateSetting::current();
        $clubFifaId = $settings->club_fifa_id ?? 396; // Default to NK Naprijed

        // Get next upcoming SENIORS match
        $nextMatch = DB::table('comet_matches')
            ->where(function ($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->where('age_category', 'SENIORS')
            ->where('date_time_local', '>=', now())
            ->orderBy('date_time_local', 'asc')
            ->first();

        // Get last completed SENIORS match
        $lastMatch = DB::table('comet_matches')
            ->where(function ($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->where('age_category', 'SENIORS')
            ->where('date_time_local', '<', now())
            ->whereNotNull('team_score_home')
            ->whereNotNull('team_score_away')
            ->orderBy('date_time_local', 'desc')
            ->first();

        // Get events from last match (goals, cards)
        $lastMatchEvents = null;
        if ($lastMatch) {
            $lastMatchEvents = DB::table('comet_match_events')
                ->where('match_fifa_id', $lastMatch->match_fifa_id)
                ->whereIn('event_type', ['goal', 'penalty_goal', 'yellow_card', 'red_card', 'yellow_red_card'])
                ->orderBy('event_minute', 'asc')
                ->get();
        }

        // Get recent match results (last 5 completed matches)
        $recentResults = DB::table('comet_matches')
            ->where(function ($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->where('age_category', 'SENIORS')
            ->where('date_time_local', '<', now())
            ->whereNotNull('team_score_home')
            ->whereNotNull('team_score_away')
            ->orderBy('date_time_local', 'desc')
            ->limit(5)
            ->get();

        // Determine seniors league competition for our club
        $mySeniorsRanking = DB::table('comet_rankings')
            ->where('team_fifa_id', $clubFifaId)
            ->where('age_category', 'SENIORS')
            ->orderByDesc('matches_played') // prioritize active league
            ->first();

        $standings = collect();
        $standingsCompetitionId = null;
        if ($mySeniorsRanking) {
            $standingsCompetitionId = $mySeniorsRanking->competition_fifa_id;
            $standings = DB::table('comet_rankings')
                ->where('competition_fifa_id', $standingsCompetitionId)
                ->orderBy('position', 'asc')
                ->get();
        }

        // Current or next matchday (SENIORS)
        $matchday = null;
        $matchdayCompetitionId = null;
        $matchdayMatches = collect();
        $matchdayLabel = null;

        if ($nextMatch) {
            $matchday = $nextMatch->match_day;
            $matchdayCompetitionId = $nextMatch->competition_fifa_id;
            $matchdayLabel = __('Nächster Spieltag');
        } elseif ($lastMatch) {
            $matchday = $lastMatch->match_day;
            $matchdayCompetitionId = $lastMatch->competition_fifa_id;
            $matchdayLabel = __('Letzter Spieltag');
        } elseif ($mySeniorsRanking) {
            // Fallback to ranking competition current round (if any)
            $matchdayCompetitionId = $mySeniorsRanking->competition_fifa_id;
        }

        if ($matchdayCompetitionId && $matchday) {
            $matchdayMatches = DB::table('comet_matches')
                ->where('competition_fifa_id', $matchdayCompetitionId)
                ->where('age_category', 'SENIORS')
                ->where('match_day', $matchday)
                ->orderBy('date_time_local', 'asc')
                ->get();
        }

        // Get top scorers (optional table)
        $topScorers = collect();
        try {
            if (Schema::connection('tenant')->hasTable('comet_top_scorers')) {
                $topScorers = DB::table('comet_top_scorers')
                    ->where('club_id', $clubFifaId)
                    ->orderBy('goals', 'desc')
                    ->select(
                        DB::raw("CONCAT(international_first_name, ' ', international_last_name) as player_name"),
                        'goals'
                    )
                    ->limit(10)
                    ->get();
            }
        } catch (\Throwable $e) {
            $topScorers = collect();
        }

        // News (placeholder for future implementation)
        $news = collect();

        // Get template slug from tenant's template relationship
        $tenant = tenant();
        $template = 'kp'; // Default fallback

        if ($tenant && $tenant->template_id) {
            $templateModel = \App\Models\Central\Template::find($tenant->template_id);
            if ($templateModel) {
                $template = $templateModel->slug;
            }
        }

        return view("templates.{$template}.home", compact(
            'settings',
            'nextMatch',
            'lastMatch',
            'lastMatchEvents',
            'recentResults',
            'standings',
            'standingsCompetitionId',
            'matchday',
            'matchdayMatches',
            'matchdayLabel',
            'topScorers',
            'news'
        ));
    }
}
