<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LandingPageTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = [
            // Site
            ['group' => 'site', 'key' => 'title', 'text' => json_encode(['de' => 'Klubportal', 'en' => 'Klubportal', 'hr' => 'Klubportal'])],
            ['group' => 'site', 'key' => 'description', 'text' => json_encode(['de' => 'Vereinsverwaltung und Webseite', 'en' => 'Club Management and Website', 'hr' => 'Upravljanje klubom i web stranica'])],
            ['group' => 'site', 'key' => 'welcome', 'text' => json_encode(['de' => 'Willkommen', 'en' => 'Welcome', 'hr' => 'Dobrodošli'])],

            // Hero Section
            ['group' => 'hero', 'key' => 'title', 'text' => json_encode(['de' => 'Ihr Verein im digitalen Zeitalter', 'en' => 'Your Club in the Digital Age', 'hr' => 'Vaš klub u digitalnom dobu'])],
            ['group' => 'hero', 'key' => 'subtitle', 'text' => json_encode(['de' => 'Professionelle Vereinsverwaltung und moderne Webseite - alles aus einer Hand', 'en' => 'Professional club management and modern website - all in one place', 'hr' => 'Profesionalno upravljanje klubom i moderna web stranica - sve na jednom mjestu'])],
            ['group' => 'hero', 'key' => 'start_free', 'text' => json_encode(['de' => 'Kostenlos starten', 'en' => 'Start for Free', 'hr' => 'Započni besplatno'])],
            ['group' => 'hero', 'key' => 'learn_more', 'text' => json_encode(['de' => 'Mehr erfahren', 'en' => 'Learn More', 'hr' => 'Saznaj više'])],
            ['group' => 'hero', 'key' => 'watch_demo', 'text' => json_encode(['de' => 'Demo ansehen', 'en' => 'Watch Demo', 'hr' => 'Pogledaj demo'])],
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

        $this->command->info('Landing page translations seeded successfully!');
    }
}
