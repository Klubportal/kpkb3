<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== SITE-BESCHREIBUNG ÜBERSETZBAR MACHEN ===\n\n";

// Erstelle Translation-Key für Site-Beschreibung
$siteDescriptionTranslation = [
    'group' => 'site',
    'key' => 'description',
    'text' => [
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
    ]
];

// Check if exists
$exists = DB::connection('central')
    ->table('language_lines')
    ->where('group', 'site')
    ->where('key', 'description')
    ->exists();

if (!$exists) {
    DB::connection('central')->table('language_lines')->insert([
        'group' => $siteDescriptionTranslation['group'],
        'key' => $siteDescriptionTranslation['key'],
        'text' => json_encode($siteDescriptionTranslation['text']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Translation-Key erstellt: site.description\n\n";
} else {
    echo "⏭️  Existiert bereits: site.description\n\n";
}

echo "📝 NÄCHSTER SCHRITT:\n";
echo "   1. Öffne die Datei wo 'site_description' verwendet wird\n";
echo "   2. Ersetze: settings()->get('site_description')\n";
echo "   3. Mit: trans('site.description')\n\n";

echo "🔍 ODER in Blade Templates:\n";
echo "   Alt: {{ settings()->get('site_description') }}\n";
echo "   Neu: {{ __('site.description') }}\n\n";

echo "🌐 Im Translation Manager bearbeiten:\n";
echo "   http://localhost:8000/admin/translation-manager\n";
echo "   Suche nach: 'site.description'\n\n";

// Zeige wo site_description verwendet wird
echo "📂 Suche nach Verwendung von 'site_description'...\n";
