<?php

namespace Database\Seeders;

use App\Models\Central\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Perfect for small clubs getting started',
                'price_monthly' => 29.00,
                'price_yearly' => 290.00,
                'max_users' => 50,
                'max_teams' => 3,
                'max_players' => 100,
                'max_storage_gb' => 5,
                'has_live_scores' => false,
                'has_statistics' => true,
                'has_pwa' => false,
                'has_custom_domain' => false,
                'has_api_access' => false,
                'is_active' => true,
                'sort_order' => 1,
                'features' => json_encode(['News & Blog', 'Team Management', 'Player Profiles', 'Match Schedule']),
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Advanced features for growing clubs',
                'price_monthly' => 79.00,
                'price_yearly' => 790.00,
                'max_users' => 200,
                'max_teams' => 10,
                'max_players' => 500,
                'max_storage_gb' => 25,
                'has_live_scores' => true,
                'has_statistics' => true,
                'has_pwa' => true,
                'has_custom_domain' => false,
                'has_api_access' => false,
                'is_active' => true,
                'sort_order' => 2,
                'features' => json_encode(['Live Scores', 'Statistics', 'PWA', 'Priority Support']),
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Complete solution for professional clubs',
                'price_monthly' => 199.00,
                'price_yearly' => 1990.00,
                'max_users' => -1,
                'max_teams' => -1,
                'max_players' => -1,
                'max_storage_gb' => 100,
                'has_live_scores' => true,
                'has_statistics' => true,
                'has_pwa' => true,
                'has_custom_domain' => true,
                'has_api_access' => true,
                'is_active' => true,
                'sort_order' => 3,
                'features' => json_encode(['Custom Domain', 'API Access', 'White Label', 'Dedicated Support']),
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }

        $this->command->info('✓ Plans seeded!');
    }
}
