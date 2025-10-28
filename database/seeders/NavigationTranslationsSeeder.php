<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NavigationTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = [
            // Navigation
            ['group' => 'nav', 'key' => 'home', 'text' => json_encode(['de' => 'Startseite', 'en' => 'Home', 'hr' => 'Početna'])],
            ['group' => 'nav', 'key' => 'register_club', 'text' => json_encode(['de' => 'Verein registrieren', 'en' => 'Register Club', 'hr' => 'Registriraj klub'])],
            ['group' => 'nav', 'key' => 'features', 'text' => json_encode(['de' => 'Funktionen', 'en' => 'Features', 'hr' => 'Značajke'])],
            ['group' => 'nav', 'key' => 'pricing', 'text' => json_encode(['de' => 'Preise', 'en' => 'Pricing', 'hr' => 'Cijene'])],
            ['group' => 'nav', 'key' => 'news', 'text' => json_encode(['de' => 'Neuigkeiten', 'en' => 'News', 'hr' => 'Vijesti'])],
            ['group' => 'nav', 'key' => 'contact', 'text' => json_encode(['de' => 'Kontakt', 'en' => 'Contact', 'hr' => 'Kontakt'])],
            ['group' => 'nav', 'key' => 'about', 'text' => json_encode(['de' => 'Über uns', 'en' => 'About', 'hr' => 'O nama'])],
            ['group' => 'nav', 'key' => 'login', 'text' => json_encode(['de' => 'Anmelden', 'en' => 'Login', 'hr' => 'Prijava'])],
        ];

        foreach ($translations as $translation) {
            DB::table('language_lines')->updateOrInsert(
                ['group' => $translation['group'], 'key' => $translation['key']],
                array_merge($translation, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Navigation translations seeded successfully!');
    }
}
