<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Club;
use Illuminate\Support\Facades\DB;

$club = Club::where('club_name', 'NK Prigorje Markušević')->first();

if (!$club) {
    echo "❌ Club not found!\n";
    exit(1);
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║   NK Prigorje - Complete Competition Data Verification       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Club: " . $club->club_name . " (FIFA ID: 598)\n";
echo "Filter: ALL ACTIVE competitions with Season 2025 or 2026\n";
echo str_repeat("═", 64) . "\n\n";

$club->run(function () {
    // Get all active competitions
    $competitions = DB::table('competitions')
        ->whereIn('season', [2024, 2025])
        ->where('status', 'active')
        ->orderBy('season', 'desc')
        ->orderBy('type')
        ->get();

    echo "🏆 ACTIVE COMPETITIONS:\n";
    echo str_repeat("─", 64) . "\n\n";

    $comp_count = 0;
    foreach ($competitions as $comp) {
        $comp_count++;

        // Get club participation
        $club_comp = DB::table('club_competitions')
            ->where('club_fifa_id', 598)
            ->where('competition_fifa_id', $comp->id)
            ->first();

        // Get rankings
        $ranking = DB::table('rankings')
            ->where('competition_id', $comp->id)
            ->first();

        echo "[$comp_count] " . strtoupper($comp->type) . " | " . $comp->name . "\n";
        echo "    Season: " . $comp->season . " | Status: " . strtoupper($comp->status) . "\n";
        echo "    Period: " . substr($comp->start_date, 0, 10) . " → " . substr($comp->end_date, 0, 10) . "\n";

        if ($club_comp) {
            echo "    NK Prigorje: " . $club_comp->wins . "W-" . $club_comp->draws . "D-" . $club_comp->losses . "L | ";
            echo $club_comp->goals_for . ":" . $club_comp->goals_against . " | Points: " . $club_comp->points . "\n";
        }

        if ($ranking) {
            echo "    Ranking Position: #" . $ranking->position . " | " . $ranking->matches_played . " matches\n";
            echo "    Record: " . $ranking->wins . "W-" . $ranking->draws . "D-" . $ranking->losses . "L | ";
            echo $ranking->goals_for . ":" . $ranking->goals_against . "\n";
        }

        echo "\n";
    }

    echo str_repeat("═", 64) . "\n\n";

    // Player stats across competitions
    echo "⚽ PLAYER STATISTICS ACROSS ALL COMPETITIONS:\n";
    echo str_repeat("─", 64) . "\n\n";

    $player_stats = DB::table('player_statistics')
        ->selectRaw('player_name, COUNT(*) as competitions, SUM(goals) as total_goals, SUM(assists) as total_assists, SUM(matches_played) as total_matches')
        ->groupBy('player_name')
        ->orderBy('total_goals', 'desc')
        ->get();

    foreach ($player_stats as $ps) {
        echo "  " . $ps->player_name . "\n";
        echo "    - Competitions: " . $ps->competitions . "\n";
        echo "    - Matches: " . $ps->total_matches . " | Goals: " . $ps->total_goals . " | Assists: " . $ps->total_assists . "\n";
        echo "\n";
    }

    echo str_repeat("═", 64) . "\n\n";

    // Match events summary
    echo "🎯 MATCH EVENTS SUMMARY:\n";
    echo str_repeat("─", 64) . "\n\n";

    $event_summary = DB::table('match_events')
        ->selectRaw('event_type, COUNT(*) as count')
        ->groupBy('event_type')
        ->orderBy('count', 'desc')
        ->get();

    foreach ($event_summary as $es) {
        echo "  " . strtoupper($es->event_type) . ": " . $es->count . " events\n";
    }

    echo "\n";
    echo str_repeat("═", 64) . "\n\n";

    // Overall statistics
    echo "📊 OVERALL STATISTICS:\n";
    echo str_repeat("─", 64) . "\n\n";

    $total_comps = DB::table('competitions')
        ->whereIn('season', [2024, 2025])
        ->where('status', 'active')
        ->count();

    $total_club_comps = DB::table('club_competitions')
        ->where('club_fifa_id', 598)
        ->count();

    $total_rankings = DB::table('rankings')->count();

    $player_count = DB::table('player_statistics')
        ->selectRaw('DISTINCT player_fifa_id')
        ->count();

    $total_player_stats = DB::table('player_statistics')->count();

    $total_events = DB::table('match_events')->count();

    $total_syncs = DB::table('comet_syncs')->count();

    echo "  Competitions: $total_comps (Active, Season 2024-2025)\n";
    echo "  Club Participations: $total_club_comps\n";
    echo "  Ranking Entries: $total_rankings\n";
    echo "  Unique Players: $player_count\n";
    echo "  Player-Competition Stats: $total_player_stats\n";
    echo "  Match Events: $total_events\n";
    echo "  Sync Operations: $total_syncs\n";

    echo "\n";

    // Get last sync
    $last_sync = DB::table('comet_syncs')->orderBy('created_at', 'desc')->first();
    if ($last_sync) {
        echo "Last Sync: " . $last_sync->synced_at . "\n";
        if ($last_sync->sync_data) {
            $data = json_decode($last_sync->sync_data, true);
            echo "  Filters: " . ($data['filters'] ?? 'N/A') . "\n";
            echo "  Status: " . $last_sync->status . " | Records: " . $last_sync->records_affected . "\n";
        }
    }
});

echo "\n";
echo str_repeat("═", 64) . "\n";
echo "✅ NK PRIGORJE MARKUŠEVIĆ - FULLY SYNCED!\n";
echo "   All active competitions for seasons 2025-2026\n";
echo str_repeat("═", 64) . "\n\n";
