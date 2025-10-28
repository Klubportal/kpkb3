<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontendController extends Controller
{
    /**
     * Homepage mit aktuellen News, kommenden Matches, Teams
     */
    public function home()
    {
        $latestNews = DB::table('news')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        $upcomingMatches = DB::table('matches')
            ->where('match_date', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('match_date', 'asc')
            ->limit(5)
            ->get();

        $teams = DB::table('teams')
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();

        return view('frontend.home', compact('latestNews', 'upcomingMatches', 'teams'));
    }

    /**
     * Über uns Seite
     */
    public function about()
    {
        return view('frontend.about');
    }

    /**
     * News Übersicht
     */
    public function news()
    {
        $news = DB::table('news')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('frontend.news.index', compact('news'));
    }

    /**
     * Einzelner News Artikel
     */
    public function newsShow($slug)
    {
        $article = DB::table('news')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$article) {
            abort(404);
        }

        // Views Counter erhöhen
        DB::table('news')
            ->where('id', $article->id)
            ->increment('views_count');

        return view('frontend.news.show', compact('article'));
    }

    /**
     * Teams Übersicht
     */
    public function teams()
    {
        $teams = DB::table('teams')
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();

        return view('frontend.teams', compact('teams'));
    }

    /**
     * Einzelnes Team mit Spielern
     */
    public function teamShow($id)
    {
        $team = DB::table('teams')->where('id', $id)->first();

        if (!$team) {
            abort(404);
        }

        $players = DB::table('players')
            ->where('team_id', $id)
            ->where('is_active', true)
            ->orderBy('jersey_number', 'asc')
            ->get();

        $matches = DB::table('matches')
            ->where('team_id', $id)
            ->orderBy('match_date', 'desc')
            ->limit(10)
            ->get();

        return view('frontend.team-show', compact('team', 'players', 'matches'));
    }

    /**
     * Matches/Spielplan
     */
    public function matches()
    {
        $upcomingMatches = DB::table('matches')
            ->where('match_date', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('match_date', 'asc')
            ->get();

        $pastMatches = DB::table('matches')
            ->where('match_date', '<', now())
            ->where('status', 'finished')
            ->orderBy('match_date', 'desc')
            ->limit(10)
            ->get();

        return view('frontend.matches', compact('upcomingMatches', 'pastMatches'));
    }

    /**
     * Kontakt
     */
    public function contact()
    {
        return view('frontend.contact');
    }
}
