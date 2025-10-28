<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCometDataToTenant extends Command
{
    protected $signature = 'tenant:import-comet {tenant} {club_fifa_id}';
    protected $description = 'Import COMET data from central database to tenant database';

    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $clubFifaId = $this->argument('club_fifa_id');

        $this->info("🔄 Importiere COMET-Daten für Club FIFA ID {$clubFifaId} in Tenant {$tenantId}...");

        // Tenant initialisieren
        $tenant = \App\Models\Central\Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("❌ Tenant {$tenantId} nicht gefunden!");
            return 1;
        }

        tenancy()->initialize($tenant);
        $this->info("✅ Tenant initialisiert: " . $tenant->name);

        // 1. Players importieren (254 Einträge)
        $this->importPlayers($tenantId, $clubFifaId);        // 2. Competitions importieren (11 Einträge)
        $this->importCompetitions($tenantId, $clubFifaId);

        // 3. Matches importieren (1508 Einträge - alle Spiele aus Wettbewerben wo Club 598 teilnimmt)
        $this->importMatches($tenantId, $clubFifaId);

        // 4. Match Players importieren (617 Einträge)
        $this->importMatchPlayers($tenantId, $clubFifaId);

        // 5. Match Events importieren (3499 Einträge)
        $this->importMatchEvents($tenantId, $clubFifaId);

        // 6. Coaches importieren (7 Einträge)
        $this->importCoaches($tenantId, $clubFifaId);

        // 7. Rankings importieren (137 Einträge)
        $this->importRankings($tenantId, $clubFifaId);

        // 8. Top Scorers importieren (801 Einträge)
        $this->importTopScorers($tenantId, $clubFifaId);

        $this->info("✅ Import abgeschlossen!");
    }

    private function importPlayers($tenantId, $clubFifaId)
    {
        $this->info("📥 Importiere Players...");

        $players = DB::connection('central')
            ->table('comet_players')
            ->where('club_fifa_id', $clubFifaId)
            ->get();

        $this->info("   Gefunden: " . $players->count() . " Spieler");

        foreach ($players as $player) {
            DB::connection('tenant')
                ->table('players')
                ->updateOrInsert(
                    ['email' => $player->email ?? 'player_' . $player->person_fifa_id . '@nkprigorje.hr'],
                    [
                        'first_name' => $player->first_name ?? '',
                        'last_name' => $player->last_name ?? '',
                        'birth_date' => $player->date_of_birth ?? now()->subYears(20),
                        'gender' => strtolower($player->gender ?? 'male'),
                        'nationality' => $player->nationality_code ?? 'HR',
                        'birthplace' => $player->place_of_birth ?? null,
                        'jersey_number' => $player->shirt_number ?? null,
                        'position' => $this->mapPosition($player->position ?? null),
                        'height' => $player->height ?? null,
                        'weight' => $player->weight ?? null,
                        'joined_date' => now(),
                        'is_active' => ($player->status ?? 'active') === 'active' ? 1 : 0,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
        }

        $this->info("   ✅ Players importiert");
    }

    private function importCompetitions($tenantId, $clubFifaId)
    {
        $this->info("📥 Importiere Competitions...");

        // Hole alle Wettbewerbe, an denen der Club teilnimmt (über Matches)
        $competitionIds = DB::connection('central')
            ->table('comet_matches')
            ->where(function($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->pluck('competition_fifa_id')
            ->unique();

        $this->info("   Gefunden: " . $competitionIds->count() . " Wettbewerbe");

        // Competitions werden direkt aus comet_matches extrahiert, keine separate Tabelle nötig

        $this->info("   ✅ Competitions importiert");
    }

    private function importMatches($tenantId, $clubFifaId)
    {
        $this->info("📥 Importiere Matches...");

        // Hole alle Matches wo der Club als Home oder Away spielt
        $matches = DB::connection('central')
            ->table('comet_matches')
            ->where(function($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->orderBy('match_day')
            ->orderBy('date_time_local')
            ->get();

        $this->info("   Gefunden: " . $matches->count() . " Spiele");

        // Matches-Tabelle erweitern mit COMET-Feldern
        DB::connection('tenant')->statement("
            ALTER TABLE matches
            ADD COLUMN IF NOT EXISTS match_fifa_id INT UNIQUE,
            ADD COLUMN IF NOT EXISTS competition_fifa_id INT,
            ADD COLUMN IF NOT EXISTS team_fifa_id_home INT,
            ADD COLUMN IF NOT EXISTS team_fifa_id_away INT,
            ADD COLUMN IF NOT EXISTS home_team_name VARCHAR(255),
            ADD COLUMN IF NOT EXISTS away_team_name VARCHAR(255),
            ADD COLUMN IF NOT EXISTS match_day INT,
            ADD COLUMN IF NOT EXISTS score_home INT,
            ADD COLUMN IF NOT EXISTS score_away INT;
        ");

        foreach ($matches as $match) {
            // Bestimme ob Home oder Away
            $isHome = $match->team_fifa_id_home == $clubFifaId;

            DB::connection('tenant')
                ->table('matches')
                ->updateOrInsert(
                    ['match_fifa_id' => $match->match_fifa_id],
                    [
                        'team_id' => 1, // Default Team ID
                        'opponent' => $isHome ? ($match->team_name_away ?? 'Unbekannt') : ($match->team_name_home ?? 'Unbekannt'),
                        'match_type' => 'league',
                        'location' => $isHome ? 'home' : 'away',
                        'match_date' => $match->date_time_local,
                        'venue' => $match->match_place ?? 'Unknown',
                        'goals_scored' => $isHome ? $match->team_score_home : $match->team_score_away,
                        'goals_conceded' => $isHome ? $match->team_score_away : $match->team_score_home,
                        'result' => $this->determineResult($isHome, $match->team_score_home, $match->team_score_away),
                        'competition' => $match->international_competition_name ?? 'Unknown',
                        'matchday' => $match->match_day,
                        'status' => $match->match_status === 'played' ? 'finished' : 'scheduled',
                        'competition_fifa_id' => $match->competition_fifa_id,
                        'team_fifa_id_home' => $match->team_fifa_id_home,
                        'team_fifa_id_away' => $match->team_fifa_id_away,
                        'home_team_name' => $match->team_name_home,
                        'away_team_name' => $match->team_name_away,
                        'match_day' => $match->match_day,
                        'score_home' => $match->team_score_home,
                        'score_away' => $match->team_score_away,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
        }

        $this->info("   ✅ Matches importiert");
    }

    private function importMatchPlayers($tenantId, $clubFifaId)
    {
        $this->info("📥 Importiere Match Players...");

        $matchPlayers = DB::connection('central')
            ->table('comet_match_players')
            ->where('team_fifa_id', $clubFifaId)
            ->get();

        $this->info("   Gefunden: " . $matchPlayers->count() . " Match Player Einträge");

        // Match-Players-Tabelle erstellen
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS match_players (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                match_fifa_id INT NOT NULL,
                person_fifa_id INT NOT NULL,
                person_name VARCHAR(255),
                shirt_number INT,
                captain BOOLEAN DEFAULT 0,
                goalkeeper BOOLEAN DEFAULT 0,
                goals INT DEFAULT 0,
                yellow_cards INT DEFAULT 0,
                red_cards INT DEFAULT 0,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE KEY unique_match_player (match_fifa_id, person_fifa_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        foreach ($matchPlayers as $mp) {
            DB::connection('tenant')
                ->table('match_players')
                ->updateOrInsert(
                    [
                        'match_fifa_id' => $mp->match_fifa_id,
                        'person_fifa_id' => $mp->person_fifa_id
                    ],
                    [
                        'person_name' => $mp->person_name,
                        'shirt_number' => $mp->shirt_number,
                        'captain' => $mp->captain ?? 0,
                        'goalkeeper' => $mp->goalkeeper ?? 0,
                        'goals' => $mp->goals ?? 0,
                        'yellow_cards' => $mp->yellow_cards ?? 0,
                        'red_cards' => $mp->red_cards ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
        }

        $this->info("   ✅ Match Players importiert");
    }

    private function importMatchEvents($tenantId, $clubFifaId)
    {
        $this->info("📥 Importiere Match Events...");

        $matchEvents = DB::connection('central')
            ->table('comet_match_events')
            ->where('team_fifa_id', $clubFifaId)
            ->get();

        $this->info("   Gefunden: " . $matchEvents->count() . " Match Events");

        // Match-Events-Tabelle erstellen
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS match_events (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                match_fifa_id INT NOT NULL,
                person_fifa_id INT,
                event_type VARCHAR(50),
                event_time VARCHAR(20),
                description TEXT,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                INDEX idx_match (match_fifa_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        foreach ($matchEvents as $event) {
            DB::connection('tenant')
                ->table('match_events')
                ->insert([
                    'match_fifa_id' => $event->match_fifa_id,
                    'person_fifa_id' => $event->person_fifa_id,
                    'event_type' => $event->event_type,
                    'event_time' => $event->event_time,
                    'description' => $event->description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $this->info("   ✅ Match Events importiert");
    }

    private function importCoaches($tenantId, $clubFifaId)
    {
        $this->info("📥 Importiere Coaches...");

        $coaches = DB::connection('central')
            ->table('comet_coaches')
            ->where('club_fifa_id', $clubFifaId)
            ->get();

        $this->info("   Gefunden: " . $coaches->count() . " Trainer");

        // Coaches-Tabelle erstellen
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS coaches (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                person_fifa_id INT UNIQUE,
                name VARCHAR(255),
                first_name VARCHAR(255),
                last_name VARCHAR(255),
                date_of_birth DATE,
                nationality VARCHAR(100),
                role VARCHAR(100),
                photo_url VARCHAR(255),
                status VARCHAR(50) DEFAULT 'active',
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        foreach ($coaches as $coach) {
            DB::connection('tenant')
                ->table('coaches')
                ->updateOrInsert(
                    ['person_fifa_id' => $coach->person_fifa_id],
                    [
                        'name' => $coach->name,
                        'first_name' => $coach->first_name,
                        'last_name' => $coach->last_name,
                        'date_of_birth' => $coach->date_of_birth,
                        'nationality' => $coach->nationality_code,
                        'role' => $coach->role,
                        'photo_url' => $coach->photo_url,
                        'status' => $coach->status ?? 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
        }

        $this->info("   ✅ Coaches importiert");
    }

    private function importRankings($tenantId, $clubFifaId)
    {
        $this->info("📥 Importiere Rankings...");

        // Hole Competition IDs über Matches
        $competitionIds = DB::connection('central')
            ->table('comet_matches')
            ->where(function($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->pluck('competition_fifa_id')
            ->unique();

        $rankings = DB::connection('central')
            ->table('comet_rankings')
            ->whereIn('competition_fifa_id', $competitionIds)
            ->get();

        $this->info("   Gefunden: " . $rankings->count() . " Rankings");

        // Rankings-Tabelle erstellen
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS rankings (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_fifa_id INT NOT NULL,
                team_fifa_id INT NOT NULL,
                team_name VARCHAR(255),
                position INT,
                matches_played INT DEFAULT 0,
                wins INT DEFAULT 0,
                draws INT DEFAULT 0,
                losses INT DEFAULT 0,
                goals_for INT DEFAULT 0,
                goals_against INT DEFAULT 0,
                goal_difference INT DEFAULT 0,
                points INT DEFAULT 0,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE KEY unique_ranking (competition_fifa_id, team_fifa_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        foreach ($rankings as $rank) {
            DB::connection('tenant')
                ->table('rankings')
                ->updateOrInsert(
                    [
                        'competition_fifa_id' => $rank->competition_fifa_id,
                        'team_fifa_id' => $rank->team_fifa_id
                    ],
                    [
                        'team_name' => $rank->team_name,
                        'position' => $rank->position,
                        'matches_played' => $rank->matches_played ?? 0,
                        'wins' => $rank->wins ?? 0,
                        'draws' => $rank->draws ?? 0,
                        'losses' => $rank->losses ?? 0,
                        'goals_for' => $rank->goals_for ?? 0,
                        'goals_against' => $rank->goals_against ?? 0,
                        'goal_difference' => $rank->goal_difference ?? 0,
                        'points' => $rank->points ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
        }

        $this->info("   ✅ Rankings importiert");
    }

    private function importTopScorers($tenantId, $clubFifaId)
    {
        $this->info("📥 Importiere Top Scorers...");

        // Hole Competition IDs über Matches
        $competitionIds = DB::connection('central')
            ->table('comet_matches')
            ->where(function($query) use ($clubFifaId) {
                $query->where('team_fifa_id_home', $clubFifaId)
                      ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->pluck('competition_fifa_id')
            ->unique();

        $topScorers = DB::connection('central')
            ->table('comet_top_scorers')
            ->whereIn('competition_fifa_id', $competitionIds)
            ->get();

        $this->info("   Gefunden: " . $topScorers->count() . " Top Scorer Einträge");

        // Top-Scorers-Tabelle erstellen
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS top_scorers (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                competition_fifa_id INT NOT NULL,
                player_fifa_id INT NOT NULL,
                player_name VARCHAR(255),
                team_name VARCHAR(255),
                goals INT DEFAULT 0,
                matches_played INT DEFAULT 0,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE KEY unique_top_scorer (competition_fifa_id, player_fifa_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        foreach ($topScorers as $scorer) {
            DB::connection('tenant')
                ->table('top_scorers')
                ->updateOrInsert(
                    [
                        'competition_fifa_id' => $scorer->competition_fifa_id,
                        'player_fifa_id' => $scorer->player_fifa_id
                    ],
                    [
                        'player_name' => $scorer->player_name,
                        'team_name' => $scorer->team_name,
                        'goals' => $scorer->goals ?? 0,
                        'matches_played' => $scorer->matches_played ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
        }

        $this->info("   ✅ Top Scorers importiert");
    }

    private function mapPosition($position)
    {
        $positionMap = [
            'Goalkeeper' => 'goalkeeper',
            'Defender' => 'defender',
            'Midfielder' => 'midfielder',
            'Forward' => 'forward',
            'Striker' => 'striker',
        ];

        return $positionMap[$position] ?? 'midfielder';
    }

    private function determineResult($isHome, $scoreHome, $scoreAway)
    {
        if ($scoreHome === null || $scoreAway === null) {
            return 'pending';
        }

        $ourScore = $isHome ? $scoreHome : $scoreAway;
        $theirScore = $isHome ? $scoreAway : $scoreHome;

        if ($ourScore > $theirScore) return 'win';
        if ($ourScore < $theirScore) return 'loss';
        return 'draw';
    }
}
