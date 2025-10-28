<?php

namespace Database\Seeders;

use App\Models\Central\NewsCategory;
use Illuminate\Database\Seeder;

class NewsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => [
                    'de' => 'Produkt-Updates',
                    'en' => 'Product Updates',
                    'hr' => 'Ažuriranja proizvoda',
                ],
                'description' => [
                    'de' => 'Neue Features und Verbesserungen',
                    'en' => 'New features and improvements',
                    'hr' => 'Nove značajke i poboljšanja',
                ],
                'slug' => 'produkt-updates',
                'icon' => 'rocket-launch',
                'color' => '#3b82f6', // Blue
                'order' => 1,
            ],
            [
                'name' => [
                    'de' => 'Success Stories',
                    'en' => 'Success Stories',
                    'hr' => 'Priče o uspjehu',
                ],
                'description' => [
                    'de' => 'Erfolgsgeschichten unserer Kunden',
                    'en' => 'Success stories from our customers',
                    'hr' => 'Priče o uspjehu naših kupaca',
                ],
                'slug' => 'success-stories',
                'icon' => 'trophy',
                'color' => '#f59e0b', // Amber
                'order' => 2,
            ],
            [
                'name' => [
                    'de' => 'Tipps & Tricks',
                    'en' => 'Tips & Tricks',
                    'hr' => 'Savjeti i trikovi',
                ],
                'description' => [
                    'de' => 'Hilfreiche Tipps zur optimalen Nutzung',
                    'en' => 'Helpful tips for optimal usage',
                    'hr' => 'Korisni savjeti za optimalnu upotrebu',
                ],
                'slug' => 'tipps-tricks',
                'icon' => 'light-bulb',
                'color' => '#10b981', // Green
                'order' => 3,
            ],
            [
                'name' => [
                    'de' => 'Ankündigungen',
                    'en' => 'Announcements',
                    'hr' => 'Objave',
                ],
                'description' => [
                    'de' => 'Wichtige Ankündigungen und News',
                    'en' => 'Important announcements and news',
                    'hr' => 'Važne objave i vijesti',
                ],
                'slug' => 'ankuendigungen',
                'icon' => 'megaphone',
                'color' => '#8b5cf6', // Purple
                'order' => 4,
            ],
            [
                'name' => [
                    'de' => 'Presse',
                    'en' => 'Press',
                    'hr' => 'Tisak',
                ],
                'description' => [
                    'de' => 'Pressemitteilungen und Medienberichte',
                    'en' => 'Press releases and media reports',
                    'hr' => 'Priopćenja za medije i izvješća',
                ],
                'slug' => 'presse',
                'icon' => 'newspaper',
                'color' => '#ef4444', // Red
                'order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            NewsCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✓ ' . count($categories) . ' News-Kategorien erstellt/aktualisiert');
    }
}
