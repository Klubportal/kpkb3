#!/bin/bash
php artisan tinker << 'PHPCODE'

// Generate test data directly in database using SQL

$games = \DB::select('SELECT comet_fifa_id as fifa_id, id FROM comet_competitions WHERE status = "ACTIVE" LIMIT 1');

if (count($games) > 0) {
    $comp = $games[0];
    $comp_id = $comp->fifa_id;
    $comp_pk = $comp->id;

    echo "Using competition $comp_id (PK: $comp_pk)\n";

    // 1. Create 5 test teams
    for ($i = 1; $i <= 5; $i++) {
        \DB::insert("INSERT INTO comet_teams (comet_id, name, team_type, age_group, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", [
            $comp_id * 100 + $i,
            "Test Team $i",
            "CLUB",
            "SENIOR"
        ]);
    }

    echo "✓ Created 5 teams\n";

    // 2. Create 20 players (4 per team)
    $teams = \DB::select('SELECT id FROM comet_teams ORDER BY id DESC LIMIT 5');
    $team_ids = array_map(function($t) { return $t->id; }, $teams);

    $player_count = 0;
    foreach ($team_ids as $team_id) {
        for ($p = 1; $p <= 4; $p++) {
            \DB::insert("INSERT INTO comet_players (comet_id, comet_team_id, first_name, last_name, full_name, jersey_number, position, birth_date, gender, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())", [
                $team_id * 1000 + $p,
                $team_id,
                "Player",
                "Test",
                "Player Test",
                $p,
                "FW",
                "1990-01-01",
                "male",
                "ACTIVE"
            ]);
            $player_count++;
        }
    }

    echo "✓ Created $player_count players\n";

    // 3. Create 5 top scorers
    $top_players = \DB::select('SELECT id FROM comet_players ORDER BY id DESC LIMIT 5');

    $rank = 1;
    foreach ($top_players as $player) {
        $goals = max(1, 10 - $rank);
        \DB::insert("INSERT INTO top_scorers (comet_id, comet_competition_id, comet_player_id, comet_team_id, player_name, team_name, rank, goals, assists, matches_played, goals_per_match, is_leading_scorer, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())", [
            $comp_id * 100 + $rank,
            $comp_pk,
            $player->id,
            1,
            "Test Player $rank",
            "Test Team",
            $rank,
            $goals,
            $rank % 2,
            5,
            $goals / 5,
            $rank == 1 ? 1 : 0
        ]);
        $rank++;
    }

    echo "✓ Created 5 top scorers\n";
    echo "\n✅ Test data generated successfully!\n";
} else {
    echo "No active competitions found\n";
}

PHPCODE
