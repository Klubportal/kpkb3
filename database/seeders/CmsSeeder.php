<?php

namespace Database\Seeders;

use App\Models\Central\News;
use App\Models\Central\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Get first existing user or create one
        $userModel = config('auth.providers.users.model');
        $admin = $userModel::first();

        if (!$admin) {
            $admin = $userModel::create([
                'name' => 'Admin',
                'email' => 'admin@klubportal.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create Tags
        $tags = ['featured', 'trending', 'important', 'sports', 'training', 'event', 'match', 'announcement'];
        foreach ($tags as $tagName) {
            Tag::findOrCreate($tagName);
        }

        // Create News Items
        $newsData = [
            [
                'title' => 'Willkommen bei Klubportal',
                'slug' => 'willkommen-bei-klubportal',
                'excerpt' => 'Wir freuen uns, unsere neue Plattform für Sportvereine vorzustellen.',
                'content' => '<h2>Eine neue Ära für Sportvereine</h2><p>Klubportal ist die moderne Lösung für die Verwaltung und Präsentation Ihres Sportvereins. Mit unserem CMS können Sie News, Events und Inhalte einfach verwalten.</p><h3>Features:</h3><ul><li>Moderne News-Verwaltung</li><li>Bildergalerien</li><li>SEO-optimiert</li><li>Mehrsprachig</li></ul>',
                'status' => 'published',
                'tags' => ['featured', 'important', 'announcement'],
            ],
            [
                'title' => 'Erfolgreicher Saisonstart',
                'slug' => 'erfolgreicher-saisonstart',
                'excerpt' => 'Unsere Mannschaft startete mit einem beeindruckenden Sieg in die neue Saison.',
                'content' => '<h2>3:0 Heimsieg</h2><p>Am vergangenen Wochenende konnte unsere erste Mannschaft einen überzeugenden 3:0 Heimsieg einfahren. Die Tore erzielten Müller (12.), Schmidt (34.) und Weber (78.).</p><p>Trainer Hans Meyer zeigte sich zufrieden: "Die Mannschaft hat eine tolle Leistung gezeigt und verdient gewonnen."</p>',
                'status' => 'published',
                'tags' => ['sports', 'match', 'featured'],
            ],
            [
                'title' => 'Neuer Trainingsplan verfügbar',
                'slug' => 'neuer-trainingsplan-verfuegbar',
                'excerpt' => 'Ab sofort steht der neue Trainingsplan für alle Mannschaften zur Verfügung.',
                'content' => '<h2>Trainingszeiten Winter 2025</h2><p>Der neue Trainingsplan für die Wintersaison 2025 ist ab sofort online verfügbar.</p><h3>Highlights:</h3><ul><li>Montag & Mittwoch: Techniktraining</li><li>Dienstag & Donnerstag: Konditionstraining</li><li>Freitag: Taktiktraining</li></ul><p>Alle Trainings finden in der Haupthalle statt.</p>',
                'status' => 'published',
                'tags' => ['training', 'announcement'],
            ],
            [
                'title' => 'Vereinsmeisterschaft angekündigt',
                'slug' => 'vereinsmeisterschaft-angekuendigt',
                'excerpt' => 'Die diesjährige Vereinsmeisterschaft findet am 15. März statt.',
                'content' => '<h2>Save the Date: 15. März 2025</h2><p>Unsere jährliche Vereinsmeisterschaft steht vor der Tür! Alle Mitglieder sind herzlich eingeladen, daran teilzunehmen.</p><h3>Kategorien:</h3><ul><li>U12 (Jahrgang 2013 und jünger)</li><li>U15 (Jahrgang 2010-2012)</li><li>U18 (Jahrgang 2007-2009)</li><li>Senioren (ab Jahrgang 2006)</li></ul><p>Anmeldungen bis zum 1. März beim Trainer.</p>',
                'status' => 'published',
                'tags' => ['event', 'important', 'trending'],
            ],
            [
                'title' => 'Neue Trikots eingetroffen',
                'slug' => 'neue-trikots-eingetroffen',
                'excerpt' => 'Die neuen Vereinstrikots für die Saison 2025 sind eingetroffen.',
                'content' => '<h2>Moderne Designs in Vereinsfarben</h2><p>Pünktlich zur neuen Saison sind unsere neuen Trikots eingetroffen. Sie können ab sofort in der Geschäftsstelle erworben werden.</p><h3>Preise:</h3><ul><li>Heimtrikot: 45€</li><li>Auswärtstrikot: 45€</li><li>Trainingstrikot: 30€</li></ul><p>Für Jugendliche gibt es 20% Rabatt!</p>',
                'status' => 'published',
                'tags' => ['announcement', 'trending'],
            ],
            [
                'title' => 'Jugendcamp im Sommer',
                'slug' => 'jugendcamp-im-sommer',
                'excerpt' => 'Unser beliebtes Sommer-Jugendcamp findet vom 15.-20. Juli statt.',
                'content' => '<h2>Eine Woche voller Action</h2><p>Vom 15. bis 20. Juli veranstalten wir wieder unser beliebtes Jugendcamp. Eine Woche voller Training, Spaß und neuen Freundschaften.</p><h3>Programm:</h3><ul><li>Tägliches Techniktraining</li><li>Freundschaftsspiele</li><li>Ausflüge</li><li>Grillabende</li></ul><p>Kosten: 150€ (inkl. Verpflegung und Übernachtung)</p>',
                'status' => 'published',
                'tags' => ['event', 'training', 'important'],
            ],
        ];

        foreach ($newsData as $index => $data) {
            $news = News::create([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['excerpt'],
                'content' => $data['content'],
                'status' => $data['status'],
                'published_at' => now()->subDays(6 - $index),
                'author_id' => $admin->id,
                'views' => rand(10, 500),
            ]);

            // Attach tags
            foreach ($data['tags'] as $tag) {
                $news->attachTag($tag);
            }
        }

        // Create Pages
        $pagesData = [
            [
                'title' => 'Über uns',
                'slug' => 'ueber-uns',
                'content' => '<h2>Willkommen bei unserem Verein</h2><p>Seit 1975 sind wir der führende Sportverein in der Region. Mit über 500 Mitgliedern und 15 Mannschaften bieten wir Sport für alle Altersgruppen.</p><h3>Unsere Geschichte</h3><p>Gegründet von einer Gruppe sportbegeisterter Freunde, hat sich unser Verein zu einer festen Größe im regionalen Sport entwickelt.</p>',
                'template' => 'about',
                'status' => 'published',
                'show_in_menu' => true,
                'menu_title' => 'Über uns',
                'order' => 1,
            ],
            [
                'title' => 'Kontakt',
                'slug' => 'kontakt',
                'content' => '<h2>Kontaktieren Sie uns</h2><p>Haben Sie Fragen oder möchten Sie Mitglied werden? Wir freuen uns auf Ihre Nachricht!</p>',
                'template' => 'contact',
                'status' => 'published',
                'show_in_menu' => true,
                'menu_title' => 'Kontakt',
                'order' => 5,
            ],
            [
                'title' => 'Mannschaften',
                'slug' => 'mannschaften',
                'content' => '<h2>Unsere Teams</h2><p>Von der U8 bis zu den Senioren - wir bieten Fußball für jedes Alter und jedes Niveau.</p><h3>Jugend</h3><ul><li>U8 bis U19 (männlich und weiblich)</li></ul><h3>Senioren</h3><ul><li>1. Mannschaft (Bezirksliga)</li><li>2. Mannschaft (Kreisliga A)</li><li>3. Mannschaft (Kreisliga B)</li></ul>',
                'template' => 'default',
                'status' => 'published',
                'show_in_menu' => true,
                'menu_title' => 'Mannschaften',
                'order' => 2,
            ],
            [
                'title' => 'Training',
                'slug' => 'training',
                'content' => '<h2>Trainingszeiten</h2><p>Hier finden Sie alle aktuellen Trainingszeiten unserer Mannschaften.</p><h3>Hauptplatz</h3><ul><li>Montag 18:00-20:00: 1. Mannschaft</li><li>Mittwoch 18:00-20:00: 2. Mannschaft</li><li>Freitag 18:00-20:00: Jugend U15-U19</li></ul><h3>Kunstrasenplatz</h3><ul><li>Dienstag 16:00-18:00: Jugend U8-U12</li><li>Donnerstag 16:00-18:00: Jugend U13-U14</li></ul>',
                'template' => 'default',
                'status' => 'published',
                'show_in_menu' => true,
                'menu_title' => 'Training',
                'order' => 3,
            ],
            [
                'title' => 'Mitglied werden',
                'slug' => 'mitglied-werden',
                'content' => '<h2>Werden Sie Teil unserer Familie</h2><p>Interessiert an einer Mitgliedschaft? Hier finden Sie alle Informationen.</p><h3>Beiträge</h3><ul><li>Kinder & Jugendliche: 5€/Monat</li><li>Erwachsene: 10€/Monat</li><li>Familien: 20€/Monat</li></ul><h3>So geht\'s</h3><ol><li>Probetraining vereinbaren</li><li>Anmeldeformular ausfüllen</li><li>Mitgliedsausweis erhalten</li></ol><p>Kontaktieren Sie uns für weitere Informationen!</p>',
                'template' => 'custom',
                'status' => 'published',
                'show_in_menu' => true,
                'menu_title' => 'Mitglied werden',
                'order' => 4,
            ],
        ];

        foreach ($pagesData as $pageData) {
            Page::create($pageData);
        }

        $this->command->info('CMS Seeder completed successfully!');
        $this->command->info('Created ' . count($newsData) . ' news items');
        $this->command->info('Created ' . count($pagesData) . ' pages');
        $this->command->info('Admin credentials: admin@klubportal.com / password');
    }
}
