<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);

echo "\n=== FÜGE LANDING PAGE MENÜ ÜBERSETZUNGEN HINZU ===\n\n";

$translations = [
    // Navigation
    ['group' => 'nav', 'key' => 'features', 'translations' => [
        'de' => 'Features',
        'en' => 'Features',
        'hr' => 'Značajke',
        'bs' => 'Karakteristike',
        'sr_Latn' => 'Karakteristike',
        'fr' => 'Fonctionnalités',
        'es' => 'Características',
        'it' => 'Funzionalità',
        'pt' => 'Recursos',
        'tr' => 'Özellikler',
    ]],
    ['group' => 'nav', 'key' => 'news', 'translations' => [
        'de' => 'News',
        'en' => 'News',
        'hr' => 'Vijesti',
        'bs' => 'Vijesti',
        'sr_Latn' => 'Vesti',
        'fr' => 'Actualités',
        'es' => 'Noticias',
        'it' => 'Notizie',
        'pt' => 'Notícias',
        'tr' => 'Haberler',
    ]],
    ['group' => 'nav', 'key' => 'pricing', 'translations' => [
        'de' => 'Preise',
        'en' => 'Pricing',
        'hr' => 'Cijene',
        'bs' => 'Cijene',
        'sr_Latn' => 'Cene',
        'fr' => 'Tarifs',
        'es' => 'Precios',
        'it' => 'Prezzi',
        'pt' => 'Preços',
        'tr' => 'Fiyatlandırma',
    ]],
    ['group' => 'nav', 'key' => 'contact', 'translations' => [
        'de' => 'Kontakt',
        'en' => 'Contact',
        'hr' => 'Kontakt',
        'bs' => 'Kontakt',
        'sr_Latn' => 'Kontakt',
        'fr' => 'Contact',
        'es' => 'Contacto',
        'it' => 'Contatto',
        'pt' => 'Contato',
        'tr' => 'İletişim',
    ]],
    ['group' => 'nav', 'key' => 'login', 'translations' => [
        'de' => 'Login',
        'en' => 'Login',
        'hr' => 'Prijava',
        'bs' => 'Prijava',
        'sr_Latn' => 'Prijava',
        'fr' => 'Connexion',
        'es' => 'Iniciar sesión',
        'it' => 'Accedi',
        'pt' => 'Entrar',
        'tr' => 'Giriş',
    ]],
    ['group' => 'nav', 'key' => 'register_club', 'translations' => [
        'de' => 'Verein registrieren',
        'en' => 'Register Club',
        'hr' => 'Registriraj klub',
        'bs' => 'Registruj klub',
        'sr_Latn' => 'Registruj klub',
        'fr' => 'Enregistrer le club',
        'es' => 'Registrar club',
        'it' => 'Registra club',
        'pt' => 'Registrar clube',
        'tr' => 'Kulüp kaydı',
    ]],
    ['group' => 'nav', 'key' => 'language', 'translations' => [
        'de' => 'Sprache / Language',
        'en' => 'Language / Sprache',
        'hr' => 'Jezik / Language',
        'bs' => 'Jezik / Language',
        'sr_Latn' => 'Jezik / Language',
        'fr' => 'Langue / Language',
        'es' => 'Idioma / Language',
        'it' => 'Lingua / Language',
        'pt' => 'Idioma / Language',
        'tr' => 'Dil / Language',
    ]],

    // Hero Section
    ['group' => 'hero', 'key' => 'start_free', 'translations' => [
        'de' => 'Jetzt kostenlos starten',
        'en' => 'Start Free Now',
        'hr' => 'Počni besplatno sada',
        'bs' => 'Počni besplatno sada',
        'sr_Latn' => 'Počni besplatno sada',
        'fr' => 'Commencer gratuitement',
        'es' => 'Empezar gratis ahora',
        'it' => 'Inizia gratis ora',
        'pt' => 'Comece grátis agora',
        'tr' => 'Şimdi ücretsiz başla',
    ]],
    ['group' => 'hero', 'key' => 'learn_more', 'translations' => [
        'de' => 'Mehr erfahren',
        'en' => 'Learn More',
        'hr' => 'Saznaj više',
        'bs' => 'Saznaj više',
        'sr_Latn' => 'Saznaj više',
        'fr' => 'En savoir plus',
        'es' => 'Saber más',
        'it' => 'Scopri di più',
        'pt' => 'Saiba mais',
        'tr' => 'Daha fazla bilgi',
    ]],
];

$added = 0;
$updated = 0;

\Illuminate\Support\Facades\DB::connection('central')->beginTransaction();

try {
    foreach ($translations as $translation) {
        $existing = \Illuminate\Support\Facades\DB::connection('central')
            ->table('language_lines')
            ->where('group', $translation['group'])
            ->where('key', $translation['key'])
            ->first();

        if ($existing) {
            \Illuminate\Support\Facades\DB::connection('central')
                ->table('language_lines')
                ->where('id', $existing->id)
                ->update([
                    'text' => json_encode($translation['translations']),
                    'updated_at' => now(),
                ]);
            $updated++;
            echo "✅ Updated: {$translation['group']}.{$translation['key']}\n";
        } else {
            \Illuminate\Support\Facades\DB::connection('central')
                ->table('language_lines')
                ->insert([
                    'group' => $translation['group'],
                    'key' => $translation['key'],
                    'text' => json_encode($translation['translations']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            $added++;
            echo "➕ Added: {$translation['group']}.{$translation['key']}\n";
        }
    }

    \Illuminate\Support\Facades\DB::connection('central')->commit();

    echo "\n=== FERTIG! ===\n";
    echo "Hinzugefügt: $added\n";
    echo "Aktualisiert: $updated\n";
    echo "Gesamt: " . ($added + $updated) . " Übersetzungen\n";

} catch (\Exception $e) {
    \Illuminate\Support\Facades\DB::connection('central')->rollBack();
    echo "\n❌ FEHLER: " . $e->getMessage() . "\n";
}

$kernel->terminate(
    Illuminate\Http\Request::create('/', 'GET'),
    new Illuminate\Http\Response()
);
