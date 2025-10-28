<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Event;
use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

/**
 * Events für Tenant
 */
class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Get first user as creator
        $creator = User::first();

        if (!$creator) {
            $this->command->warn('   ⚠️ No user found - skipping events');
            return;
        }

        $events = [
            [
                'created_by_user_id' => $creator->id,
                'title' => 'Jahreshauptversammlung',
                'description' => 'Unsere jährliche Mitgliederversammlung.',
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(30)->addHours(3),
                'all_day' => false,
                'location' => 'Vereinsheim',
                'type' => 'meeting',
                'visibility' => 'members_only',
                'status' => 'scheduled',
            ],
            [
                'created_by_user_id' => $creator->id,
                'title' => 'Trainingscamp',
                'description' => 'Sommercamp für alle Jugendspieler.',
                'start_date' => now()->addDays(60),
                'end_date' => now()->addDays(67),
                'all_day' => true,
                'location' => 'Sportschule Musterstadt',
                'type' => 'tournament',
                'visibility' => 'public',
                'status' => 'scheduled',
            ],
            [
                'created_by_user_id' => $creator->id,
                'title' => 'Sponsorenabend',
                'description' => 'Dankeschön an unsere Sponsoren.',
                'start_date' => now()->addDays(45),
                'end_date' => now()->addDays(45)->addHours(4),
                'all_day' => false,
                'location' => 'Stadion VIP Lounge',
                'type' => 'social',
                'visibility' => 'members_only',
                'status' => 'scheduled',
            ],
        ];

        foreach ($events as $eventData) {
            Event::firstOrCreate(
                ['title' => $eventData['title']],
                $eventData
            );
        }

        $this->command->info('   ✅ Events created');
    }
}
