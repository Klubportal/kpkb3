<?php

namespace Database\Seeders;

use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $plans = Plan::all();

        if ($plans->isEmpty()) {
            $this->command->error('No plans found! Please run PlansSeeder first.');
            return;
        }

        $tenants = [
            [
                'id' => 'fcbarcelona',
                'plan' => 'Enterprise',
                'trial' => null,
                'active' => true,
            ],
            [
                'id' => 'realmadrid',
                'plan' => 'Enterprise',
                'trial' => null,
                'active' => true,
            ],
            [
                'id' => 'bayernmunich',
                'plan' => 'Pro',
                'trial' => null,
                'active' => true,
            ],
            [
                'id' => 'juventus',
                'plan' => 'Pro',
                'trial' => now()->addDays(10),
                'active' => true,
            ],
            [
                'id' => 'manchesterutd',
                'plan' => 'Basic',
                'trial' => now()->addDays(5),
                'active' => true,
            ],
            [
                'id' => 'chelsea',
                'plan' => 'Basic',
                'trial' => now()->addDays(20),
                'active' => true,
            ],
            [
                'id' => 'psg',
                'plan' => 'Pro',
                'trial' => null,
                'active' => true,
            ],
            [
                'id' => 'liverpool',
                'plan' => 'Basic',
                'trial' => now()->addDays(3),
                'active' => true,
            ],
            [
                'id' => 'atleticomadrid',
                'plan' => 'Basic',
                'trial' => now()->addDays(25),
                'active' => false,
            ],
            [
                'id' => 'arsenal',
                'plan' => 'Pro',
                'trial' => null,
                'active' => true,
            ],
        ];

        foreach ($tenants as $tenantData) {
            $plan = $plans->firstWhere('name', $tenantData['plan']);

            if (!$plan) {
                $this->command->warn("Plan '{$tenantData['plan']}' not found for tenant '{$tenantData['id']}'");
                continue;
            }

            // Update or create tenant
            $tenant = Tenant::updateOrCreate(
                ['id' => $tenantData['id']],
                [
                    'plan_id' => $plan->id,
                    'trial_ends_at' => $tenantData['trial'],
                    'is_active' => $tenantData['active'],
                ]
            );

            // Create subdomain
            $tenant->domains()->firstOrCreate([
                'domain' => $tenantData['id'] . '.localhost',
            ]);

            $this->command->info("Tenant '{$tenantData['id']}' created/updated with plan '{$plan->name}'");
        }        $this->command->info('✓ Tenant seeding completed!');
    }
}
