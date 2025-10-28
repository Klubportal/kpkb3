<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('central')->table('templates')->insert([
            [
                'name' => 'kp',
                'display_name' => 'Klubportal Standard',
                'description' => 'Modernes, responsive Template mit Football-UI Integration. Perfekt für Fußballvereine mit vollständiger COMET-Integration.',
                'preview_image' => 'templates/kp-preview.jpg',
                'features' => json_encode([
                    'Responsive Design',
                    'Dark/Light Mode',
                    'COMET Live-Daten',
                    'Match Center',
                    'Player Statistics',
                    'News System',
                    'Photo Gallery',
                    'Contact Forms',
                    'Social Media Integration',
                    'Multi-Language Support'
                ]),
                'color_scheme' => json_encode([
                    'primary' => '#1e40af',
                    'secondary' => '#dc2626',
                    'accent' => '#f59e0b',
                    'neutral' => '#1f2937',
                    'base' => '#ffffff',
                ]),
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'modern',
                'display_name' => 'Modern Pro',
                'description' => 'Minimalistisches Design mit Fokus auf Geschwindigkeit und Performance.',
                'preview_image' => 'templates/modern-preview.jpg',
                'features' => json_encode([
                    'Minimal Design',
                    'Fast Loading',
                    'Clean Layout',
                    'Mobile First',
                ]),
                'color_scheme' => json_encode([
                    'primary' => '#0ea5e9',
                    'secondary' => '#8b5cf6',
                    'accent' => '#f97316',
                    'neutral' => '#0f172a',
                    'base' => '#ffffff',
                ]),
                'is_active' => false, // Noch nicht implementiert
                'is_default' => false,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'classic',
                'display_name' => 'Classic Sport',
                'description' => 'Traditionelles Design im klassischen Vereinslook.',
                'preview_image' => 'templates/classic-preview.jpg',
                'features' => json_encode([
                    'Traditional Layout',
                    'Club Heritage Design',
                    'Classic Navigation',
                ]),
                'color_scheme' => json_encode([
                    'primary' => '#15803d',
                    'secondary' => '#be123c',
                    'accent' => '#d97706',
                    'neutral' => '#374151',
                    'base' => '#f9fafb',
                ]),
                'is_active' => false, // Noch nicht implementiert
                'is_default' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
