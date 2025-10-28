<?php

use Illuminate\Support\Facades\DB;
use App\Models\Central\Tenant;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$clubFifaId = 598; // NK Prigorjem
$tenantId = 'nkprigorjem';

echo "🔄 Synchronisiere COMET-Daten für Club FIFA ID {$clubFifaId} in Tenant {$tenantId}...\n\n";

$tenant = Tenant::find($tenantId);
if (!$tenant) {
    die("❌ Tenant {$tenantId} nicht gefunden!\n");
}

tenancy()->initialize($tenant);

// SCHRITT 1: Competitions - Alle Wettbewerbe in denen Club 598 spielt
echo "1️⃣  Lade Competitions...\n";

// Finde competitions anhand der Matches wo 598 home oder away spielt
$competitionIds = DB::connection('central')
    ->table('comet_matches')
    ->where('team_fifa_id_home', $clubFifaId)
    ->orWhere('team_fifa_id_away', $clubFifaId)
    ->distinct()
    ->pluck('competition_fifa_id');

echo "   Gefunden: {$competitionIds->count()} Wettbewerbe (IDs: {$competitionIds->implode(', ')})\n";

$competitions = DB::connection('central')
    ->table('comet_competitions')
    ->whereIn('comet_id', $competitionIds)
    ->get();

foreach ($competitions as $comp) {
    // Jetzt haben Tenant und Central dieselbe Struktur - alle Spalten kopieren
    DB::table('comet_competitions')->updateOrInsert(
        ['comet_id' => $comp->comet_id],
        (array) $comp
    );
}
echo "   ✓ {$competitions->count()} Competitions kopiert\n\n";

// SCHRITT 2: Matches - Alle Spiele aus diesen Wettbewerben
echo "2️⃣  Lade Matches...\n";
$matches = DB::connection('central')
    ->table('comet_matches')
    ->whereIn('competition_fifa_id', $competitionIds)
    ->get();

echo "   Gefunden: {$matches->count()} Spiele\n";

foreach ($matches as $match) {
    DB::table('comet_matches')->updateOrInsert(
        ['match_fifa_id' => $match->match_fifa_id],
        (array) $match
    );
}
echo "   ✓ {$matches->count()} Matches kopiert\n\n";

// SCHRITT 3: Players - Spieler vom Club 598
echo "3️⃣  Lade Players...\n";
$players = DB::connection('central')
    ->table('comet_players')
    ->where('club_fifa_id', $clubFifaId)
    ->get();

echo "   Gefunden: {$players->count()} Spieler\n";

foreach ($players as $player) {
    DB::table('comet_players')->updateOrInsert(
        ['person_fifa_id' => $player->person_fifa_id],
        (array) $player
    );
}
echo "   ✓ {$players->count()} Players kopiert\n\n";

// SCHRITT 4: Match Players - Spieler-Einsätze in allen Matches
echo "4️⃣  Lade Match Players...\n";
$matchIds = $matches->pluck('match_fifa_id');

$matchPlayers = DB::connection('central')
    ->table('comet_match_players')
    ->whereIn('match_fifa_id', $matchIds)
    ->get();

echo "   Gefunden: {$matchPlayers->count()} Match Players\n";

foreach ($matchPlayers as $mp) {
    // team_nature berechnen falls leer
    if (empty($mp->team_nature)) {
        // Finde das Match um zu bestimmen ob HOME oder AWAY
        $match = $matches->firstWhere('match_fifa_id', $mp->match_fifa_id);
        if ($match) {
            $mp->team_nature = $mp->team_fifa_id == $match->team_fifa_id_home ? 'HOME' : 'AWAY';
        } else {
            $mp->team_nature = 'HOME'; // Fallback
        }
    }

    DB::table('comet_match_players')->updateOrInsert(
        ['match_fifa_id' => $mp->match_fifa_id, 'person_fifa_id' => $mp->person_fifa_id, 'team_fifa_id' => $mp->team_fifa_id],
        (array) $mp
    );
}
echo "   ✓ {$matchPlayers->count()} Match Players kopiert\n\n";

// SCHRITT 5: Match Events - Ereignisse in allen Matches
echo "5️⃣  Lade Match Events...\n";
$matchEvents = DB::connection('central')
    ->table('comet_match_events')
    ->whereIn('match_fifa_id', $matchIds)
    ->get();

echo "   Gefunden: {$matchEvents->count()} Match Events\n";

// Tabelle leeren und neu befüllen (Events haben keine eindeutige ID)
DB::table('comet_match_events')->whereIn('match_fifa_id', $matchIds)->delete();

foreach ($matchEvents as $event) {
    DB::table('comet_match_events')->insert((array) $event);
}
echo "   ✓ {$matchEvents->count()} Match Events kopiert\n\n";

// SCHRITT 6: Rankings - Tabellenstände der Competitions
echo "6️⃣  Lade Rankings...\n";
$rankings = DB::connection('central')
    ->table('comet_rankings')
    ->whereIn('competition_fifa_id', $competitionIds)
    ->get();

echo "   Gefunden: {$rankings->count()} Rankings\n";

foreach ($rankings as $ranking) {
    DB::table('comet_rankings')->updateOrInsert(
        ['competition_fifa_id' => $ranking->competition_fifa_id, 'team_fifa_id' => $ranking->team_fifa_id],
        (array) $ranking
    );
}
echo "   ✓ {$rankings->count()} Rankings kopiert\n\n";

// SCHRITT 7: Top Scorers - Torschützenliste der Competitions
echo "7️⃣  Lade Top Scorers...\n";
$topScorers = DB::connection('central')
    ->table('comet_top_scorers')
    ->whereIn('competition_fifa_id', $competitionIds)
    ->get();

echo "   Gefunden: {$topScorers->count()} Top Scorers\n";

foreach ($topScorers as $scorer) {
    // Unique key: id (auto_increment) - verwende updateOrInsert mit player_fifa_id + competition
    $existing = DB::table('comet_top_scorers')
        ->where('competition_fifa_id', $scorer->competition_fifa_id)
        ->where('player_fifa_id', $scorer->player_fifa_id)
        ->first();

    if ($existing) {
        DB::table('comet_top_scorers')
            ->where('id', $existing->id)
            ->update((array) $scorer);
    } else {
        DB::table('comet_top_scorers')->insert((array) $scorer);
    }
}
echo "   ✓ {$topScorers->count()} Top Scorers kopiert\n\n";

// SCHRITT 8: Coaches - Trainer vom Club 598
echo "8️⃣  Lade Coaches...\n";
$coaches = DB::connection('central')
    ->table('comet_coaches')
    ->where('club_fifa_id', $clubFifaId)
    ->get();

echo "   Gefunden: {$coaches->count()} Coaches\n";

foreach ($coaches as $coach) {
    DB::table('comet_coaches')->updateOrInsert(
        ['person_fifa_id' => $coach->person_fifa_id, 'club_fifa_id' => $coach->club_fifa_id],
        (array) $coach
    );
}
echo "   ✓ {$coaches->count()} Coaches kopiert\n\n";

tenancy()->end();

echo "✅ Synchronisation abgeschlossen!\n\n";
echo "📊 Zusammenfassung:\n";
echo "   - Competitions: {$competitions->count()}\n";
echo "   - Matches: {$matches->count()}\n";
echo "   - Players: {$players->count()}\n";
echo "   - Match Players: {$matchPlayers->count()}\n";
echo "   - Match Events: {$matchEvents->count()}\n";
echo "   - Rankings: {$rankings->count()}\n";
echo "   - Top Scorers: {$topScorers->count()}\n";
echo "   - Coaches: {$coaches->count()}\n";
