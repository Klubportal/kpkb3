<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\News;
use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

/**
 * News für Tenant
 */
class TenantNewsSeeder extends Seeder
{
    public function run(): void
    {
        // Get first user as author
        $author = User::first();

        if (!$author) {
            $this->command->warn('   ⚠️ No user found - skipping news');
            return;
        }

        $newsItems = [
            [
                'author_user_id' => $author->id,
                'title' => 'Erfolgreicher Saisonstart',
                'slug' => 'erfolgreicher-saisonstart',
                'excerpt' => 'Unsere Mannschaft startete erfolgreich in die neue Saison.',
                'content' => '<p>Am vergangenen Wochenende konnte unsere erste Mannschaft einen überzeugenden 3:0 Heimsieg einfahren.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'is_featured' => true,
                'allow_comments' => true,
            ],
            [
                'author_user_id' => $author->id,
                'title' => 'Neuer Trainer vorgestellt',
                'slug' => 'neuer-trainer-vorgestellt',
                'excerpt' => 'Wir freuen uns, unseren neuen Cheftrainer vorzustellen.',
                'content' => '<p>Hans Meyer übernimmt ab sofort die erste Mannschaft.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(14),
                'is_featured' => false,
                'allow_comments' => true,
            ],
            [
                'author_user_id' => $author->id,
                'title' => 'Sommerfest 2025',
                'slug' => 'sommerfest-2025',
                'excerpt' => 'Unser jährliches Sommerfest findet am 15. Juli statt.',
                'content' => '<p>Alle Mitglieder und Freunde des Vereins sind herzlich eingeladen!</p>',
                'status' => 'draft',
                'is_featured' => false,
                'allow_comments' => true,
            ],
        ];

        foreach ($newsItems as $newsData) {
            News::firstOrCreate(
                ['slug' => $newsData['slug']],
                $newsData
            );
        }

        $this->command->info('   ✅ News items created');
    }
}
