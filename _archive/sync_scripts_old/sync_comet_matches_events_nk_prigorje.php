<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\Comet\CometMatchEvent;
use App\Models\Comet\CometMatch;
use App\Models\Comet\CometCompetition;
use App\Services\CometApiService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🏟️  SYNC COMET MATCH EVENTS - NK PRIGORJE\n";
echo str_repeat("=", 60) . "\n\n";

$api = new CometApiService();
$totalEvents = 0;
$newEvents = 0;
$errorCount = 0;

// Hole alle Competition IDs aus der Datenbank
$competitionIds = CometCompetition::pluck('comet_id')->toArray();
echo "📋 Competitions in DB: " . count($competitionIds) . "\n";

// Datum-Filter: vom ersten Match Day bis morgen
$tomorrow = Carbon::tomorrow()->endOfDay();
echo "📅 Syncen bis: " . $tomorrow->format('Y-m-d') . "\n\n";

// Hole alle fertigen Matches die zu unseren Competitions gehören und bis morgen stattfinden
$matches = CometMatch::where('match_status', 'played')
    ->whereIn('competition_fifa_id', $competitionIds)
    ->where('date_time_local', '<=', $tomorrow)
    ->orderBy('match_fifa_id')
    ->get();

echo "📊 Gefundene fertige Matches: " . $matches->count() . "\n\n";

foreach ($matches as $match) {
    echo "⚽ Match {$match->match_fifa_id}: {$match->team_name_home} vs {$match->team_name_away}\n";

    try {
        // API Call
        $apiEvents = $api->getMatchEvents($match->match_fifa_id);

        if (empty($apiEvents)) {
            echo "   ℹ️  Keine Events gefunden\n";
            continue;
        }

        echo "   📥 API Events: " . count($apiEvents) . "\n";

        foreach ($apiEvents as $event) {
            $totalEvents++;

            // Determine match team (HOME or AWAY) and team_fifa_id
            $matchTeam = $event['matchTeam'] ?? null;
            $teamFifaId = null;

            if ($matchTeam === 'HOME') {
                $teamFifaId = $match->team_fifa_id_home;
            } elseif ($matchTeam === 'AWAY') {
                $teamFifaId = $match->team_fifa_id_away;
            }

            // Map event type from API
            $eventType = mapEventType($event['eventType'] ?? 'unknown');

            // Extract data
            $eventData = [
                'match_event_fifa_id' => $event['id'] ?? null,
                'match_fifa_id' => $match->match_fifa_id,
                'competition_fifa_id' => $match->competition_fifa_id,
                'player_fifa_id' => $event['playerFifaId'] ?? null,
                'player_name' => $event['personName'] ?? $event['localPersonName'] ?? null,
                'shirt_number' => $event['shirtNumber'] ?? null,
                'player_fifa_id_2' => $event['playerFifaId2'] ?? null,
                'player_name_2' => $event['personName2'] ?? $event['localPersonName2'] ?? null,
                'team_fifa_id' => $teamFifaId,
                'match_team' => $matchTeam,
                'event_type' => $eventType,
                'event_minute' => $event['minute'] ?? 0,
                'description' => $event['eventDetailType'] ?? null,
            ];

            // Skip if no valid event ID
            if (!$eventData['match_event_fifa_id']) {
                continue;
            }

            // Update or create
            CometMatchEvent::updateOrCreate(
                ['match_event_fifa_id' => $eventData['match_event_fifa_id']],
                $eventData
            );

            $newEvents++;
        }

        echo "   ✅ Events gespeichert\n";

        // Rate limiting
        usleep(100000); // 100ms pause

    } catch (\Exception $e) {
        echo "   ❌ Fehler: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ SYNC ABGESCHLOSSEN\n\n";
echo "📊 Statistik:\n";
echo "   - Matches geprüft: " . $matches->count() . "\n";
echo "   - Total API Events: {$totalEvents}\n";
echo "   - Events gespeichert: {$newEvents}\n";
echo "   - Fehler: {$errorCount}\n";

function mapEventType($apiType) {
    $mapping = [
        'GOAL' => 'goal',
        'PENALTY' => 'penalty_goal',
        'OWN_GOAL' => 'own_goal',
        'YELLOW' => 'yellow_card',
        'RED' => 'red_card',
        'YELLOW_RED' => 'yellow_red_card',
        'SUBSTITUTION' => 'substitution',
        'PENALTY_MISSED' => 'penalty_missed',
    ];

    return $mapping[strtoupper($apiType)] ?? 'goal';
}
