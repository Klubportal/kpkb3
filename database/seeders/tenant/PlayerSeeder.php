<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Player;
use App\Models\Tenant\Team;
use Illuminate\Database\Seeder;

/**
 * Players für Tenant
 */
class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::where('name', 'Erste Mannschaft')->first();

        if (!$team) {
            $this->command->warn('   ⚠️  Erste Mannschaft not found - skipping players');
            return;
        }

        $players = [
            [
                'first_name' => 'Max',
                'last_name' => 'Mustermann',
                'jersey_number' => '1',
                'position' => 'goalkeeper',
                'birth_date' => '1995-05-15',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subYear(),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Tom',
                'last_name' => 'Schmidt',
                'jersey_number' => '2',
                'position' => 'defender',
                'birth_date' => '1996-03-22',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subYear(),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Jonas',
                'last_name' => 'Weber',
                'jersey_number' => '3',
                'position' => 'defender',
                'birth_date' => '1997-07-10',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subMonths(6),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Leon',
                'last_name' => 'Becker',
                'jersey_number' => '4',
                'position' => 'center_back',
                'birth_date' => '1994-11-30',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subYear(),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Lukas',
                'last_name' => 'Fischer',
                'jersey_number' => '5',
                'position' => 'midfielder',
                'birth_date' => '1998-02-18',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subMonths(3),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Felix',
                'last_name' => 'Wagner',
                'jersey_number' => '6',
                'position' => 'defensive_midfielder',
                'birth_date' => '1995-09-05',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subYear(),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Tim',
                'last_name' => 'Hoffmann',
                'jersey_number' => '7',
                'position' => 'left_winger',
                'birth_date' => '1999-04-12',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subMonths(8),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Paul',
                'last_name' => 'Richter',
                'jersey_number' => '8',
                'position' => 'central_midfielder',
                'birth_date' => '1996-12-25',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subYear(),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Klein',
                'jersey_number' => '9',
                'position' => 'striker',
                'birth_date' => '1997-06-08',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subMonths(4),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Moritz',
                'last_name' => 'Schröder',
                'jersey_number' => '10',
                'position' => 'attacking_midfielder',
                'birth_date' => '1998-08-14',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subYear(),
                'is_active' => true,
                'status' => 'active',
            ],
            [
                'first_name' => 'Jan',
                'last_name' => 'Neumann',
                'jersey_number' => '11',
                'position' => 'right_winger',
                'birth_date' => '1999-01-20',
                'gender' => 'male',
                'nationality' => 'Deutschland',
                'joined_date' => now()->subMonths(5),
                'is_active' => true,
                'status' => 'active',
            ],
        ];

        foreach ($players as $playerData) {
            Player::firstOrCreate(
                [
                    'team_id' => $team->id,
                    'jersey_number' => $playerData['jersey_number']
                ],
                array_merge($playerData, ['team_id' => $team->id])
            );
        }

        $this->command->info('   ✅ Players created for ' . $team->name);
    }
}
