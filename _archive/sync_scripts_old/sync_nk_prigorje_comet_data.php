<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CometApiService;
use App\Models\Comet\CometCompetition;
use App\Models\Comet\CometClubExtended;
use App\Models\Comet\CometPlayer;
use App\Models\Comet\CometMatch;
use App\Models\Comet\CometTopScorer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$api = new CometApiService();

// NK Prigorje Configuration
$teamFifaId = 598;           // NK Prigorje Team FIFA ID (KORRIGIERT!)
$clubFifaId = 598;           // NK Prigorje Club FIFA ID
$organisationFifaIds = [1, 10]; // HNS (1) and Zagrebački NS (10)
$seasons = [2025, 2026];     // Target seasons

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   COMET API SYNC - NK PRIGORJE                               ║\n";
echo "║   Team FIFA ID: 598 | Club FIFA ID: 598                     ║\n";
echo "║   Season: 2025/2026 | Status: ACTIVE                        ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Test Connection
echo "🔌 Testing API Connection...\n";
if (!$api->testConnection()) {
    die("❌ API Connection failed! Check credentials in .env\n");
}
echo "✅ API Connection successful!\n\n";

// ================================
// 1. SYNC COMPETITIONS
// ================================
echo "1️⃣  SYNCING COMPETITIONS\n";
echo str_repeat('─', 70) . "\n";

try {
    // Use teamFifaId parameter to get only NK Prigorje competitions
    $allCompetitions = $api->getCompetitions([
        'teamFifaId' => $teamFifaId,
        'active' => true,
    ]);
    echo "   Fetched " . count($allCompetitions) . " competitions for Team $teamFifaId\n";

    $syncedCompetitions = [];
    $count = 0;

    foreach ($allCompetitions as $comp) {
        // Filter by season (2025, 2026)
        $season = $comp['season'] ?? '';
        if (!str_contains($season, '2025') && !str_contains($season, '2026')) {
            continue;
        }

        $competition = CometCompetition::updateOrCreate(
            ['comet_id' => $comp['competitionFifaId']],
            [
                'organisation_fifa_id' => $comp['organisationFifaId'] ?? null,
                'name' => $comp['internationalName'],
                'slug' => Str::slug($comp['internationalName'] . '-' . $comp['season']),
                'country' => 'HRV',
                'logo_url' => null, // TODO: Generate from imageId
                'image_id' => $comp['imageId'] ?? null,
                'type' => strtolower($comp['competitionType'] ?? 'league'),
                'season' => (string)$comp['season'],
                'status' => $comp['status'] === 'ACTIVE' ? 'active' : 'finished',
                'active' => $comp['status'] === 'ACTIVE',
                'age_category' => $comp['ageCategory'] ?? null,
                'team_character' => $comp['teamCharacter'] ?? null,
                'nature' => $comp['nature'] ?? null,
                'gender' => $comp['gender'] ?? null,
                'match_type' => $comp['matchType'] ?? null,
                'participants' => $comp['numberOfParticipants'] ?? null,
                'start_date' => isset($comp['dateFrom']) ? date('Y-m-d', strtotime($comp['dateFrom'])) : null,
                'end_date' => isset($comp['dateTo']) ? date('Y-m-d', strtotime($comp['dateTo'])) : null,
                'local_names' => $comp['localNames'] ?? null,
                'settings' => [
                    'penalty_shootout' => $comp['penaltyShootout'] ?? false,
                    'flying_substitutions' => $comp['flyingSubstitutions'] ?? false,
                    'discipline' => $comp['discipline'] ?? 'FOOTBALL',
                ],
            ]
        );

        $syncedCompetitions[] = $competition;
        $count++;
        echo "  ✓ {$comp['internationalName']} ({$comp['season']})\n";

        // Insert into comet_club_competitions to link club 598 to this competition
        DB::connection('central')->table('comet_club_competitions')->updateOrInsert(
            [
                'competitionFifaId' => $comp['competitionFifaId'],
            ],
            [
                'ageCategory' => $comp['ageCategory'] ?? 'OTHER',
                'ageCategoryName' => $comp['ageCategoryName'] ?? $comp['ageCategory'] ?? 'OTHER',
                'internationalName' => $comp['internationalName'],
                'season' => (int)$comp['season'],
                'status' => $comp['status'] ?? 'ACTIVE',
                'flag_played_matches' => null,
                'flag_scheduled_matches' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    echo "\n✅ Synced {$count} competitions\n\n";} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    die();
}

// ================================
// 2. SYNC TEAMS
// ================================
echo "2️⃣  SYNCING TEAMS\n";
echo str_repeat('─', 70) . "\n";

$nkPrigorjeTeams = [];

foreach ($syncedCompetitions as $competition) {
    try {
        $teams = $api->getCompetitionTeams($competition->comet_id);

        foreach ($teams as $team) {
            // Only NK Prigorje teams
            if ($team['organisationFifaId'] != $clubFifaId) {
                continue;
            }

            $club = CometClubExtended::updateOrCreate(
                ['club_fifa_id' => $team['teamFifaId']],
                [
                    'organisation_fifa_id' => $team['organisationFifaId'],
                    'fifa_id' => $team['teamFifaId'],
                    'name' => $team['internationalName'],
                    'short_name' => $team['internationalShortName'] ?? null,
                    'country' => $team['country'] ?? 'HR',
                    'city' => $team['town'] ?? null,
                    'region' => $team['region'] ?? null,
                    'status' => $team['status'] ?? 'ACTIVE',
                    'facility_fifa_id' => $team['facilityFifaId'] ?? null,
                    'local_names' => $team['localNames'] ?? null,
                    'is_synced' => true,
                    'last_synced_at' => now(),
                ]
            );

            $nkPrigorjeTeams[] = $club;
            echo "  ✓ {$team['internationalName']} (Team FIFA ID: {$team['teamFifaId']})\n";
        }

    } catch (\Exception $e) {
        echo "  ⚠️  {$competition->name}: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Synced " . count($nkPrigorjeTeams) . " teams\n\n";

// ================================
// 3. ENSURE NK PRIGORJE CLUB EXISTS
// ================================
echo "3️⃣  ENSURING NK PRIGORJE CLUB RECORD EXISTS\n";
echo str_repeat('─', 70) . "\n";

try {
    $nkPrigorjeClub = CometClubExtended::updateOrCreate(
        ['club_fifa_id' => $clubFifaId],
        [
            'fifa_id' => $clubFifaId, // Same as club_fifa_id for now
            'name' => 'NK Prigorje',
            'short_name' => 'Prigorje',
            'city' => 'Zabok',
            'region' => 'Krapinsko-zagorska',
            'country' => 'HRV',
            'is_synced' => true,
            'last_synced_at' => now(),
        ]
    );

    echo "  ✓ NK Prigorje (FIFA ID: {$clubFifaId})\n";
    echo "\n✅ Club record created/updated\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    die();
}

// ================================
// 4. SYNC PLAYERS
// ================================
echo "4️⃣  SYNCING PLAYERS\n";
echo str_repeat('─', 70) . "\n";

$playerCount = 0;

try {
    $players = $api->getTeamPlayers($teamFifaId, 'ACTIVE');

    foreach ($players as $player) {
        CometPlayer::updateOrCreate(
            ['person_fifa_id' => $player['personFifaId']],
            [
                'club_fifa_id' => $clubFifaId,
                'first_name' => $player['internationalFirstName'] ?? '',
                'last_name' => $player['internationalLastName'] ?? '',
                'name' => trim(($player['internationalFirstName'] ?? '') . ' ' . ($player['internationalLastName'] ?? '')),
                'popular_name' => $player['popularName'] ?? null,
                'date_of_birth' => $player['dateOfBirth'] ?? null,
                'place_of_birth' => $player['placeOfBirth'] ?? null,
                'country_of_birth' => $player['countryOfBirth'] ?? null,
                'nationality' => $player['nationality'] ?? null,
                'nationality_code' => $player['nationality'] ?? null,
                'gender' => $player['gender'] ?? null,
                'position' => match($player['playerPosition'] ?? 'Unknown') {
                    'Goalkeeper' => 'goalkeeper',
                    'Defender' => 'defender',
                    'Midfielder' => 'midfielder',
                    'Forward' => 'forward',
                    default => 'unknown',
                },
                'status' => strtolower($player['status'] ?? 'active'),
                'local_names' => $player['localPersonNames'] ?? null,
                'is_synced' => true,
                'last_synced_at' => now(),
            ]
        );

        $playerCount++;
        $name = trim(($player['internationalFirstName'] ?? '') . ' ' . ($player['internationalLastName'] ?? ''));
        $position = $player['playerPosition'] ?? 'Unknown';
        echo "  ✓ {$name} ({$position})\n";
    }

    echo "\n✅ Synced {$playerCount} players\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// ================================
// 5. SYNC MATCHES
// ================================
echo "5️⃣  SYNCING MATCHES\n";
echo str_repeat('─', 70) . "\n";

$matchCount = 0;

foreach ($syncedCompetitions as $competition) {
    try {
        $matches = $api->getCompetitionMatches($competition->comet_id);

        foreach ($matches as $match) {
            // Only matches with NK Prigorje
            $hasNkPrigorje = false;
            $homeTeam = null;
            $awayTeam = null;

            foreach ($match['matchTeams'] ?? [] as $team) {
                if ($team['teamFifaId'] == $teamFifaId) {
                    $hasNkPrigorje = true;
                }
                if ($team['teamNature'] == 'HOME') {
                    $homeTeam = $team;
                }
                if ($team['teamNature'] == 'AWAY') {
                    $awayTeam = $team;
                }
            }

            if (!$hasNkPrigorje || !$homeTeam || !$awayTeam) {
                continue;
            }

            // Calculate scores from phases
            $phases = $match['matchPhases'] ?? [];
            $finalPhase = end($phases);
            $firstHalf = null;
            foreach ($phases as $phase) {
                if ($phase['phase'] == 'FIRST_HALF') {
                    $firstHalf = $phase;
                    break;
                }
            }

            CometMatch::updateOrCreate(
                ['comet_id' => $match['matchFifaId']],
                [
                    'competition_id' => $competition->id,
                    'competition_fifa_id' => $match['competitionFifaId'],
                    'home_club_fifa_id' => $homeTeam['teamFifaId'],
                    'away_club_fifa_id' => $awayTeam['teamFifaId'],
                    'kickoff_time' => $match['dateTimeLocal'] ?? null,
                    'status' => match($match['status'] ?? 'SCHEDULED') {
                        'PLAYED' => 'finished',
                        'SCHEDULED' => 'scheduled',
                        'POSTPONED' => 'postponed',
                        'CANCELLED' => 'cancelled',
                        default => 'scheduled',
                    },
                    'match_type' => $match['matchType'] ?? null,
                    'nature' => $match['nature'] ?? null,
                    'home_goals' => $finalPhase['homeScore'] ?? null,
                    'away_goals' => $finalPhase['awayScore'] ?? null,
                    'home_goals_ht' => $firstHalf['homeScore'] ?? null,
                    'away_goals_ht' => $firstHalf['awayScore'] ?? null,
                    'facility_fifa_id' => $match['facilityFifaId'] ?? null,
                    'attendance' => $match['attendance'] ?? null,
                    'match_day' => $match['matchDay'] ?? null,
                    'round' => isset($match['matchDay']) ? "Matchday {$match['matchDay']}" : null,
                    'extra_time' => json_encode([
                        'all_phases' => $phases,
                        'match_officials' => $match['matchOfficials'] ?? [],
                    ]),
                ]
            );

            $matchCount++;
            $score = isset($finalPhase) ? "{$finalPhase['homeScore']}:{$finalPhase['awayScore']}" : "vs";
            echo "  ✓ {$homeTeam['internationalName']} {$score} {$awayTeam['internationalName']}\n";
        }

    } catch (\Exception $e) {
        echo "  ⚠️  {$competition->name}: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Synced {$matchCount} matches\n\n";

// ================================
// 6. SYNC TOP SCORERS
// ================================
echo "6️⃣  SYNCING TOP SCORERS\n";
echo str_repeat('─', 70) . "\n";

$scorerCount = 0;

foreach ($syncedCompetitions as $competition) {
    try {
        $topScorers = $api->getCompetitionTopScorers($competition->comet_id);

        foreach ($topScorers as $scorer) {
            // Only NK Prigorje players
            if (($scorer['clubId'] ?? 0) != $clubFifaId) {
                continue;
            }

            CometTopScorer::updateOrCreate(
                [
                    'competition_id' => $competition->id,
                    'person_fifa_id' => $scorer['playerFifaId'],
                ],
                [
                    'club_fifa_id' => $scorer['clubId'] ?? null,
                    'team_fifa_id' => $scorer['teamId'] ?? null,
                    'first_name' => $scorer['internationalFirstName'] ?? '',
                    'last_name' => $scorer['internationalLastName'] ?? '',
                    'popular_name' => $scorer['popularName'] ?? null,
                    'club_name' => $scorer['club'] ?? null,
                    'team_name' => $scorer['team'] ?? null,
                    'goals' => $scorer['goals'] ?? 0,
                    'penalties' => 0, // TODO: Extract from match events
                    'position' => null, // Will be calculated
                    'api_data' => $scorer,
                ]
            );

            $scorerCount++;
            $name = trim(($scorer['internationalFirstName'] ?? '') . ' ' . ($scorer['internationalLastName'] ?? ''));
            echo "  ✓ {$name} - {$scorer['goals']} goals\n";
        }

    } catch (\Exception $e) {
        echo "  ⚠️  {$competition->name}: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Synced {$scorerCount} top scorers\n\n";

// ================================
// SUMMARY
// ================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                       SYNC SUMMARY                            ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";
echo sprintf("║ %-30s %30d ║\n", "Competitions", count($syncedCompetitions));
echo sprintf("║ %-30s %30d ║\n", "Teams", count($nkPrigorjeTeams));
echo sprintf("║ %-30s %30d ║\n", "Players", $playerCount);
echo sprintf("║ %-30s %30d ║\n", "Matches", $matchCount);
echo sprintf("║ %-30s %30d ║\n", "Top Scorers", $scorerCount);
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "✅ SYNC COMPLETED SUCCESSFULLY!\n";
echo "📊 Data is now in kpkb3 (Central DB)\n\n";
