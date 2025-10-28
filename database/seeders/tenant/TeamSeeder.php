<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Team;
use Illuminate\Database\Seeder;

/**
 * Teams für Tenant
 */
class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Erste Mannschaft',
                'age_group' => 'Senioren',
                'gender' => 'male',
                'league' => 'Bundesliga',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Zweite Mannschaft',
                'age_group' => 'Senioren',
                'gender' => 'male',
                'league' => 'Regionalliga',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'U19',
                'age_group' => 'U19',
                'gender' => 'male',
                'league' => 'A-Junioren Bundesliga',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'U17',
                'age_group' => 'U17',
                'gender' => 'male',
                'league' => 'B-Junioren Bundesliga',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Frauen',
                'age_group' => 'Senioren',
                'gender' => 'female',
                'league' => 'Frauen-Bundesliga',
                'is_active' => true,
                'display_order' => 5,
            ],
        ];

        foreach ($teams as $teamData) {
            Team::firstOrCreate(
                ['name' => $teamData['name']],
                $teamData
            );
        }

        $this->command->info('   ✅ Teams created');
    }
}
