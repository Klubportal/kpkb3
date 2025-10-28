<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\FootballMatch;
use App\Models\Tenant\Team;
use Illuminate\Database\Seeder;

/**
 * Matches für Tenant
 */
class MatchSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::where('name', 'Erste Mannschaft')->first();

        if (!$team) {
            $this->command->warn('   ⚠️  Erste Mannschaft not found - skipping matches');
            return;
        }

        $matches = [
            [
                'team_id' => $team->id,
                'opponent' => 'FC Beispiel',
                'match_date' => now()->addDays(7),
                'location' => 'home',
                'match_type' => 'league',
                'competition' => 'Bundesliga',
                'status' => 'scheduled',
                'result' => 'pending',
            ],
            [
                'team_id' => $team->id,
                'opponent' => 'SV Musterstadt',
                'match_date' => now()->subDays(7),
                'location' => 'away',
                'match_type' => 'league',
                'competition' => 'Bundesliga',
                'status' => 'finished',
                'result' => 'win',
                'goals_scored' => 2,
                'goals_conceded' => 1,
            ],
            [
                'team_id' => $team->id,
                'opponent' => 'TSV Testverein',
                'match_date' => now()->addDays(14),
                'location' => 'home',
                'match_type' => 'cup',
                'competition' => 'DFB-Pokal',
                'status' => 'scheduled',
                'result' => 'pending',
            ],
        ];

        foreach ($matches as $matchData) {
            FootballMatch::firstOrCreate(
                [
                    'team_id' => $matchData['team_id'],
                    'opponent' => $matchData['opponent'],
                    'match_date' => $matchData['match_date'],
                ],
                $matchData
            );
        }

        $this->command->info('   ✅ Matches created');
    }
}
