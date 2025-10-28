<?php

use App\Models\CometCompetition;
use App\Models\CometTeam;
use App\Models\CometPlayer;
use App\Models\CometMatch;
use App\Models\CometMatchEvent;
use App\Models\TopScorer;
use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/bootstrap/providers.php';

/*
 * ============================================================================
 * COMET DATA GENERATOR - Generates realistic test data for all NK Prigorje competitions
 * ============================================================================
 *
 * Since the Comet API only provides competition metadata (no matches/players),
 * this script generates realistic Croatian football data for testing purposes.
 *
 * Generated data:
 * - Teams (2-16 per competition)
 * - Players (15-30 per team)
 * - Matches (realistic schedule)
 * - Match Events (goals, assists, yellow/red cards)
 * - Top Scorers (calculated from match events)
 */

class CometDataGenerator
{
    protected $competitions = [];
    protected $teams = [];
    protected $players = [];
    protected $croatianFirstNames = [
        'Marko', 'Ivan', 'Petar', 'Ante', 'Damir', 'Luka', 'Nikola', 'Tomislav', 'Goran', 'Zoran',
        'Filip', 'Sandro', 'Mateo', 'Stefan', 'Roberto', 'Vedran', 'Zvonimir', 'Dražen', 'Milan', 'Igor',
        'Vlado', 'Boris', 'Alen', 'Marin', 'Danko', 'Krešo', 'Franko', 'Rajko', 'Vojislav', 'Slaven'
    ];

    protected $croatianLastNames = [
        'Čolić', 'Horvat', 'Novak', 'Ćorluka', 'Modrić', 'Rakitić', 'Mandžukić', 'Petković', 'Vukčević', 'Srbljak',
        'Šmić', 'Božić', 'Kovačić', 'Pranjić', 'Jerković', 'Mikulić', 'Jurčević', 'Pavlović', 'Šarić', 'Brkljača',
        'Strinić', 'Lovren', 'Vida', 'Subašić', 'Đurčić', 'Kaladić', 'Mitrović', 'Ćorluka', 'Vukčević', 'Bradarić'
    ];

    protected $positions = ['GK', 'LB', 'CB', 'RB', 'LWB', 'RWB', 'CDM', 'CM', 'CAM', 'LM', 'RM', 'ST', 'LW', 'RW', 'CF'];

    public function __construct()
    {
        $this->loadCompetitions();
    }

    protected function loadCompetitions()
    {
        $this->competitions = CometCompetition::where('status', 'ACTIVE')
            ->where('organisationFifaId', 598)
            ->get()
            ->toArray();

        echo "Loaded " . count($this->competitions) . " active competitions\n";
    }

    public function generate()
    {
        echo "\n【 Generating Realistic Comet Data 】\n";
        echo str_repeat("=", 80) . "\n\n";

        DB::beginTransaction();

        try {
            // Clean existing data
            $this->cleanExistingData();

            // Generate teams for each competition
            $this->generateTeams();

            // Generate players for each team
            $this->generatePlayers();

            // Generate matches
            $this->generateMatches();

            // Generate match events and calculate goals
            $this->generateMatchEvents();

            // Calculate and populate top scorers
            $this->generateTopScorers();

            DB::commit();

            echo "\n" . str_repeat("=", 80) . "\n";
            echo "✅ Data generation completed successfully!\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Error during data generation: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString();
            exit(1);
        }
    }

    protected function cleanExistingData()
    {
        echo "Step 1: Cleaning existing data...\n";

        TopScorer::truncate();
        CometMatchEvent::truncate();
        CometMatch::truncate();
        CometPlayer::truncate();
        CometTeam::truncate();

        echo "  ✓ Cleaned all existing data\n\n";
    }

    protected function generateTeams()
    {
        echo "Step 2: Generating teams...\n";

        $teamCount = 0;
        foreach ($this->competitions as $comp) {
            $compId = $comp['id'];
            $fifaId = $comp['comet_fifa_id'];
            $numTeams = (int)($comp['number_of_participants'] ?? mt_rand(8, 16));

            for ($i = 0; $i < $numTeams; $i++) {
                $team = CometTeam::create([
                    'comet_competition_id' => $compId,
                    'comet_fifa_id' => $fifaId . '-' . ($i + 1),
                    'team_name' => $this->generateTeamName(),
                    'short_name' => strtoupper(substr($this->croatianLastNames[array_rand($this->croatianLastNames)], 0, 3)),
                    'city' => ['Zagreb', 'Split', 'Rijeka', 'Osijek', 'Zadar', 'Pula', 'Varaždin', 'Velika Gorica', 'Karlovac', 'Čakovec'][array_rand(['Zagreb', 'Split', 'Rijeka', 'Osijek', 'Zadar', 'Pula', 'Varaždin', 'Velika Gorica', 'Karlovac', 'Čakovec'])],
                    'country' => 'HR',
                ]);

                $this->teams[$team->id] = $team;
                $teamCount++;
            }
        }

        echo "  ✓ Generated " . $teamCount . " teams\n\n";
    }

    protected function generatePlayers()
    {
        echo "Step 3: Generating players...\n";

        $playerCount = 0;
        foreach ($this->teams as $team) {
            $numPlayers = mt_rand(18, 30);
            $positions = $this->positions;
            shuffle($positions);

            for ($i = 0; $i < $numPlayers; $i++) {
                $player = CometPlayer::create([
                    'comet_team_id' => $team->id,
                    'comet_fifa_id' => $team->comet_fifa_id . '-P' . ($i + 1),
                    'player_name' => $this->generatePlayerName(),
                    'number' => $i + 1,
                    'position' => $positions[$i % count($positions)],
                    'birth_date' => date('Y-m-d', strtotime('-' . mt_rand(18, 40) . ' years')),
                ]);

                $this->players[$player->id] = $player;
                $playerCount++;
            }
        }

        echo "  ✓ Generated " . $playerCount . " players\n\n";
    }

    protected function generateMatches()
    {
        echo "Step 4: Generating matches and schedule...\n";

        $matchCount = 0;
        foreach ($this->competitions as $comp) {
            $compId = $comp['id'];
            $teams = CometTeam::where('comet_competition_id', $compId)->get();

            if ($teams->count() < 2) {
                continue;
            }

            // Create round-robin schedule (simplified - not all matches)
            $teamsArray = $teams->all();
            $matchdays = 2; // Only generate 2 match days for testing

            for ($day = 1; $day <= $matchdays; $day++) {
                // Create some matches per day
                $numMatches = mt_rand(3, min(8, intval($teams->count() / 2)));

                for ($m = 0; $m < $numMatches; $m++) {
                    $homeTeam = $teamsArray[array_rand($teamsArray)];
                    $awayTeam = $teamsArray[array_rand($teamsArray)];

                    if ($homeTeam->id === $awayTeam->id) {
                        continue;
                    }

                    $match = CometMatch::create([
                        'comet_competition_id' => $compId,
                        'comet_fifa_id' => $comp['comet_fifa_id'] . '-M' . ($matchCount + 1),
                        'home_team_id' => $homeTeam->id,
                        'away_team_id' => $awayTeam->id,
                        'home_team_name' => $homeTeam->team_name,
                        'away_team_name' => $awayTeam->team_name,
                        'home_goals' => mt_rand(0, 4),
                        'away_goals' => mt_rand(0, 4),
                        'match_date' => date('Y-m-d H:i:s', strtotime('+' . (4 + $day) . ' days')),
                        'status' => 'FINISHED',
                        'match_day' => $day,
                    ]);

                    $matchCount++;
                }
            }
        }

        echo "  ✓ Generated " . $matchCount . " matches\n\n";
    }

    protected function generateMatchEvents()
    {
        echo "Step 5: Generating match events and goals...\n";

        $eventCount = 0;
        $matches = CometMatch::where('status', 'FINISHED')->get();

        foreach ($matches as $match) {
            // Get team rosters
            $homeTeamPlayers = CometPlayer::where('comet_team_id', $match->home_team_id)
                ->where('position', '<>', 'GK')
                ->inRandomOrder()
                ->take(10)
                ->get();

            $awayTeamPlayers = CometPlayer::where('comet_team_id', $match->away_team_id)
                ->where('position', '<>', 'GK')
                ->inRandomOrder()
                ->take(10)
                ->get();

            // Generate home team goals
            for ($g = 0; $g < $match->home_goals; $g++) {
                if ($homeTeamPlayers->count() === 0) break;

                $scorer = $homeTeamPlayers->random();
                CometMatchEvent::create([
                    'comet_match_id' => $match->id,
                    'comet_player_id' => $scorer->id,
                    'comet_team_id' => $match->home_team_id,
                    'event_type' => 'GOAL',
                    'minute' => mt_rand(1, 90),
                    'player_name' => $scorer->player_name,
                    'team_name' => $match->home_team_name,
                ]);
                $eventCount++;

                // Possible assist
                if (mt_rand(0, 1)) {
                    $assister = $homeTeamPlayers->where('id', '<>', $scorer->id)->random();
                    CometMatchEvent::create([
                        'comet_match_id' => $match->id,
                        'comet_player_id' => $assister->id,
                        'comet_team_id' => $match->home_team_id,
                        'event_type' => 'ASSIST',
                        'minute' => mt_rand(1, 90),
                        'player_name' => $assister->player_name,
                        'team_name' => $match->home_team_name,
                    ]);
                    $eventCount++;
                }
            }

            // Generate away team goals
            for ($g = 0; $g < $match->away_goals; $g++) {
                if ($awayTeamPlayers->count() === 0) break;

                $scorer = $awayTeamPlayers->random();
                CometMatchEvent::create([
                    'comet_match_id' => $match->id,
                    'comet_player_id' => $scorer->id,
                    'comet_team_id' => $match->away_team_id,
                    'event_type' => 'GOAL',
                    'minute' => mt_rand(1, 90),
                    'player_name' => $scorer->player_name,
                    'team_name' => $match->away_team_name,
                ]);
                $eventCount++;

                // Possible assist
                if (mt_rand(0, 1)) {
                    $assister = $awayTeamPlayers->where('id', '<>', $scorer->id)->random();
                    CometMatchEvent::create([
                        'comet_match_id' => $match->id,
                        'comet_player_id' => $assister->id,
                        'comet_team_id' => $match->away_team_id,
                        'event_type' => 'ASSIST',
                        'minute' => mt_rand(1, 90),
                        'player_name' => $assister->player_name,
                        'team_name' => $match->away_team_name,
                    ]);
                    $eventCount++;
                }
            }
        }

        echo "  ✓ Generated " . $eventCount . " match events\n\n";
    }

    protected function generateTopScorers()
    {
        echo "Step 6: Calculating and populating top scorers...\n";

        // Get all competitions
        $competitions = CometCompetition::where('status', 'ACTIVE')
            ->where('organisationFifaId', 598)
            ->get();

        $topScorerCount = 0;

        foreach ($competitions as $competition) {
            // Count goals by player in this competition
            $goalsByPlayer = DB::table('comet_match_events')
                ->join('comet_matches', 'comet_match_events.comet_match_id', '=', 'comet_matches.id')
                ->join('comet_players', 'comet_match_events.comet_player_id', '=', 'comet_players.id')
                ->join('comet_teams', 'comet_match_events.comet_team_id', '=', 'comet_teams.id')
                ->where('comet_matches.comet_competition_id', $competition->id)
                ->where('comet_match_events.event_type', 'GOAL')
                ->select(
                    'comet_players.id as player_id',
                    'comet_match_events.comet_player_id',
                    'comet_teams.id as team_id',
                    'comet_match_events.player_name',
                    'comet_teams.team_name',
                    DB::raw('COUNT(*) as goal_count'),
                    DB::raw('COUNT(DISTINCT comet_matches.id) as matches_played')
                )
                ->groupBy('comet_players.id', 'comet_teams.id')
                ->orderByDesc('goal_count')
                ->get();

            // Create top scorer records
            $rank = 1;
            foreach ($goalsByPlayer as $scorer) {
                $topScorer = TopScorer::create([
                    'comet_id' => $competition->comet_fifa_id . '-' . $scorer->comet_player_id,
                    'comet_competition_id' => $competition->id,
                    'comet_player_id' => $scorer->player_id,
                    'comet_team_id' => $scorer->team_id,
                    'player_name' => $scorer->player_name,
                    'team_name' => $scorer->team_name,
                    'rank' => $rank++,
                    'goals' => $scorer->goal_count,
                    'assists' => 0, // Could calculate from ASSIST events
                    'matches_played' => $scorer->matches_played,
                    'goals_per_match' => round($scorer->goal_count / $scorer->matches_played, 2),
                    'is_leading_scorer' => $rank === 2 ? true : false, // Mark rank 1 as leading
                    'notes' => 'Generated test data',
                ]);

                $topScorerCount++;
            }
        }

        echo "  ✓ Generated " . $topScorerCount . " top scorer records\n\n";
    }

    protected function generateTeamName()
    {
        $prefixes = ['NK', 'OFK', 'HŠK', 'DNK'];
        $suffix = $this->croatianLastNames[array_rand($this->croatianLastNames)];
        return $prefixes[array_rand($prefixes)] . ' ' . $suffix;
    }

    protected function generatePlayerName()
    {
        $firstName = $this->croatianFirstNames[array_rand($this->croatianFirstNames)];
        $lastName = $this->croatianLastNames[array_rand($this->croatianLastNames)];
        return $firstName . ' ' . $lastName;
    }
}

// Run generator
$generator = new CometDataGenerator();
$generator->generate();
