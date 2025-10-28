<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\TemplateSetting;

class HomeController extends Controller
{
    public function index()
    {
        $settings = TemplateSetting::current();
        $clubFifaId = $settings->club_fifa_id ?? 396; // Default to NK Naprijed

        // Get next upcoming match
        $nextMatch = DB::table('comet_matches')
            ->where(function($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->where('date_time_local', '>=', now())
            ->orderBy('date_time_local', 'asc')
            ->first();

        // Get last completed match
        $lastMatch = DB::table('comet_matches')
            ->where(function($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
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

        // Get last 6 completed matches
        $recentResults = DB::table('comet_matches')
            ->where(function($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->where('date_time_local', '<', now())
            ->whereNotNull('team_score_home')
            ->whereNotNull('team_score_away')
            ->orderBy('date_time_local', 'desc')
            ->limit(6)
            ->get();

        // Get current league standings
        $standings = DB::table('comet_rankings')
            ->orderBy('position', 'asc')
            ->limit(20)
            ->get();

        // Get top scorers from comet_top_scorers table
        $topScorers = DB::table('comet_top_scorers')
            ->where('club_id', $clubFifaId)
            ->orderBy('goals', 'desc')
            ->select(
                DB::raw("CONCAT(international_first_name, ' ', international_last_name) as player_name"),
                'goals'
            )
            ->limit(10)
            ->get();

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
            'topScorers',
            'news'
        ));
    }
}
