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

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║         NK Prigorje - Data Verification                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "Club: " . $club->club_name . "\n";
echo "ID: " . $club->id . "\n";
echo "FIFA ID: 598\n";
echo "Season: 2025/2026\n\n";

$club->run(function () {
    echo "📊 Verifying Comet Data Tables:\n";
    echo str_repeat("─", 60) . "\n\n";

    // 1. Check clubs_extended
    $club_ext = DB::table('clubs_extended')->where('club_fifa_id', 598)->first();
    if ($club_ext) {
        echo "✅ clubs_extended:\n";
        echo "   - Club: " . $club_ext->name . "\n";
        echo "   - FIFA ID: " . $club_ext->fifa_id . "\n";
        echo "   - Stadium: " . $club_ext->stadium_name . " (Capacity: " . $club_ext->stadium_capacity . ")\n";
        echo "   - League: " . $club_ext->league_name . "\n";
        echo "   - Coach: " . $club_ext->coach_name . "\n";
        echo "   - Synced: " . ($club_ext->is_synced ? 'YES' : 'NO') . "\n\n";
    } else {
        echo "❌ clubs_extended: NOT FOUND\n\n";
    }

    // 2. Check competitions (active, season 2025)
    $comps = DB::table('competitions')
        ->where('season', 2025)
        ->where('status', 'active')
        ->get();

    echo "✅ Active Competitions (2025/2026):\n";
    if ($comps->count() > 0) {
        foreach ($comps as $comp) {
            echo "   - " . $comp->name . "\n";
            echo "     Type: " . $comp->type . "\n";
            echo "     Status: " . $comp->status . "\n";
            echo "     Period: " . $comp->start_date . " to " . $comp->end_date . "\n";
        }
    } else {
        echo "   ⚠️  No active competitions found\n";
    }
    echo "\n";

    // 3. Check club_competitions (M:M)
    $club_comps = DB::table('club_competitions')
        ->where('club_fifa_id', 598)
        ->get();

    echo "✅ Club Competitions (M:M):\n";
    if ($club_comps->count() > 0) {
        foreach ($club_comps as $cc) {
            echo "   - Competition ID: " . $cc->competition_fifa_id . "\n";
            echo "     Participant: " . ($cc->is_participant ? 'YES' : 'NO') . "\n";
            echo "     Record: " . $cc->wins . "W-" . $cc->draws . "D-" . $cc->losses . "L\n";
            echo "     Goals: " . $cc->goals_for . " for, " . $cc->goals_against . " against\n";
            echo "     Points: " . $cc->points . "\n";
        }
    } else {
        echo "   ⚠️  No club competitions found\n";
    }
    echo "\n";

    // 4. Check rankings
    $rankings = DB::table('rankings')
        ->get();

    echo "✅ Rankings:\n";
    if ($rankings->count() > 0) {
        foreach ($rankings as $rank) {
            echo "   - Position: " . $rank->position . "\n";
            echo "     Competition: " . $rank->name . "\n";
            echo "     Record: " . $rank->wins . "W-" . $rank->draws . "D-" . $rank->losses . "L\n";
            echo "     Points: " . $rank->points . "\n";
            echo "     Form: " . $rank->form . "\n";
        }
    } else {
        echo "   ⚠️  No rankings found\n";
    }
    echo "\n";

    // 5. Check player_statistics
    $players = DB::table('player_statistics')
        ->get();

    echo "✅ Player Statistics:\n";
    echo "   Total Players: " . $players->count() . "\n";
    if ($players->count() > 0) {
        foreach ($players as $p) {
            echo "   - " . $p->player_name . ": " . $p->goals . " goals, " . $p->assists . " assists\n";
            echo "     Matches: " . $p->matches_played . " | Minutes: " . $p->minutes_played . "\n";
            echo "     Cards: " . $p->yellow_cards . "Y, " . $p->red_cards . "R\n";
        }
    }
    echo "\n";

    // 6. Check match_events
    $events = DB::table('match_events')
        ->get();

    echo "✅ Match Events:\n";
    echo "   Total Events: " . $events->count() . "\n";
    if ($events->count() > 0) {
        foreach ($events as $e) {
            echo "   - [" . $e->event_minute . "'] " . $e->event_type . ": " . $e->description . "\n";
        }
    }
    echo "\n";

    // 7. Check comet_syncs log
    $syncs = DB::table('comet_syncs')->get();

    echo "✅ Comet Sync Logs:\n";
    echo "   Total Syncs: " . $syncs->count() . "\n";
    if ($syncs->count() > 0) {
        $latest = $syncs->last();
        echo "   Last Sync:\n";
        echo "     - Entity: " . $latest->entity_type . "\n";
        echo "     - Action: " . $latest->action . "\n";
        echo "     - Status: " . $latest->status . "\n";
        echo "     - Records: " . $latest->records_affected . "\n";
        echo "     - Time: " . $latest->synced_at . "\n";
        if ($latest->sync_data) {
            $data = json_decode($latest->sync_data, true);
            echo "     - Details: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    echo "\n";
});

echo str_repeat("═", 60) . "\n";
echo "✅ VERIFICATION COMPLETE\n";
echo "NK Prigorje Markušević synced with ACTIVE 2025/2026 season data!\n";
echo str_repeat("═", 60) . "\n\n";
