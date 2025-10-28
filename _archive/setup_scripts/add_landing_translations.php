<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== LANDING PAGE ÜBERSETZUNGEN ERSTELLEN ===\n\n";

$translations = [
    // Hero Section
    ['group' => 'landing', 'key' => 'hero.title', 'text' => [
        'de' => 'Deine Fußballverein Management Platform',
        'en' => 'Your Football Club Management Platform',
        'hr' => 'Tvoja platforma za upravljanje nogometnim klubom',
        'bs' => 'Vaša platforma za upravljanje fudbalskim klubom',
        'sr_Latn' => 'Vaša platforma za upravljanje fudbalskim klubom',
        'es' => 'Tu plataforma de gestión de clubes de fútbol',
        'fr' => 'Votre plateforme de gestion de club de football',
        'it' => 'La tua piattaforma di gestione del club calcistico',
        'pt' => 'Sua plataforma de gestão de clubes de futebol',
        'tr' => 'Futbol Kulübü Yönetim Platformunuz'
    ]],

    ['group' => 'landing', 'key' => 'hero.subtitle', 'text' => [
        'de' => 'Alles was du brauchst, um deinen Verein professionell zu verwalten',
        'en' => 'Everything you need to manage your club professionally',
        'hr' => 'Sve što vam je potrebno za profesionalno upravljanje vašim klubom',
        'bs' => 'Sve što vam je potrebno za profesionalno upravljanje vašim klubom',
        'sr_Latn' => 'Sve što vam je potrebno za profesionalno upravljanje vašim klubom',
        'es' => 'Todo lo que necesitas para gestionar tu club profesionalmente',
        'fr' => 'Tout ce dont vous avez besoin pour gérer votre club professionnellement',
        'it' => 'Tutto ciò di cui hai bisogno per gestire professionalmente il tuo club',
        'pt' => 'Tudo o que você precisa para gerenciar seu clube profissionalmente',
        'tr' => 'Kulübünüzü profesyonel bir şekilde yönetmek için ihtiyacınız olan her şey'
    ]],

    ['group' => 'landing', 'key' => 'hero.cta', 'text' => [
        'de' => 'Jetzt starten',
        'en' => 'Get Started',
        'hr' => 'Započni sada',
        'bs' => 'Započni sada',
        'sr_Latn' => 'Započni sada',
        'es' => 'Comenzar ahora',
        'fr' => 'Commencer maintenant',
        'it' => 'Inizia ora',
        'pt' => 'Comece agora',
        'tr' => 'Şimdi başla'
    ]],

    // Features
    ['group' => 'landing', 'key' => 'features.title', 'text' => [
        'de' => 'Funktionen',
        'en' => 'Features',
        'hr' => 'Značajke',
        'bs' => 'Karakteristike',
        'sr_Latn' => 'Karakteristike',
        'es' => 'Características',
        'fr' => 'Fonctionnalités',
        'it' => 'Funzionalità',
        'pt' => 'Recursos',
        'tr' => 'Özellikler'
    ]],

    ['group' => 'landing', 'key' => 'features.news', 'text' => [
        'de' => 'Neuigkeiten & Artikel',
        'en' => 'News & Articles',
        'hr' => 'Vijesti i članci',
        'bs' => 'Vijesti i članci',
        'sr_Latn' => 'Vesti i članci',
        'es' => 'Noticias y artículos',
        'fr' => 'Actualités et articles',
        'it' => 'Notizie e articoli',
        'pt' => 'Notícias e artigos',
        'tr' => 'Haberler ve Makaleler'
    ]],

    ['group' => 'landing', 'key' => 'features.matches', 'text' => [
        'de' => 'Spielverwaltung',
        'en' => 'Match Management',
        'hr' => 'Upravljanje utakmicama',
        'bs' => 'Upravljanje utakmicama',
        'sr_Latn' => 'Upravljanje utakmicama',
        'es' => 'Gestión de partidos',
        'fr' => 'Gestion des matchs',
        'it' => 'Gestione delle partite',
        'pt' => 'Gestão de partidas',
        'tr' => 'Maç Yönetimi'
    ]],

    ['group' => 'landing', 'key' => 'features.members', 'text' => [
        'de' => 'Mitgliederverwaltung',
        'en' => 'Member Management',
        'hr' => 'Upravljanje članovima',
        'bs' => 'Upravljanje članovima',
        'sr_Latn' => 'Upravljanje članovima',
        'es' => 'Gestión de miembros',
        'fr' => 'Gestion des membres',
        'it' => 'Gestione dei membri',
        'pt' => 'Gestão de membros',
        'tr' => 'Üye Yönetimi'
    ]],

    ['group' => 'landing', 'key' => 'features.statistics', 'text' => [
        'de' => 'Statistiken & Analysen',
        'en' => 'Statistics & Analytics',
        'hr' => 'Statistike i analize',
        'bs' => 'Statistike i analize',
        'sr_Latn' => 'Statistike i analize',
        'es' => 'Estadísticas y análisis',
        'fr' => 'Statistiques et analyses',
        'it' => 'Statistiche e analisi',
        'pt' => 'Estatísticas e análises',
        'tr' => 'İstatistikler ve Analizler'
    ]],
];

$inserted = 0;

foreach ($translations as $translation) {
    // Check if exists
    $exists = DB::connection('central')
        ->table('language_lines')
        ->where('group', $translation['group'])
        ->where('key', $translation['key'])
        ->exists();

    if (!$exists) {
        DB::connection('central')->table('language_lines')->insert([
            'group' => $translation['group'],
            'key' => $translation['key'],
            'text' => json_encode($translation['text']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inserted++;
        echo "✅ Erstellt: {$translation['group']}.{$translation['key']}\n";
    } else {
        echo "⏭️  Existiert bereits: {$translation['group']}.{$translation['key']}\n";
    }
}

echo "\n📊 ZUSAMMENFASSUNG:\n";
echo "   - Neue Einträge: $inserted\n";
echo "   - Jeder Eintrag enthält 10 Sprachen\n";
echo "   - Gesamt neue Übersetzungen: " . ($inserted * 10) . "\n\n";

echo "✅ FERTIG!\n\n";
echo "🌐 Jetzt im Translation Manager bearbeiten:\n";
echo "   http://localhost:8000/admin/translation-manager\n";
echo "   Suche nach: 'landing'\n\n";
