<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== DIREKTER FORM-RENDER-TEST ===\n\n";

// Simuliere einen authentifizierten Benutzer
$user = \App\Models\Tenant\User::first();
if (!$user) {
    echo "✗ Kein User gefunden!\n";
    exit;
}

auth('tenant')->login($user);
echo "✓ Eingeloggt als: {$user->name}\n\n";

// Lade das TemplateSetting Model
$record = \App\Models\Tenant\TemplateSetting::first();
if (!$record) {
    echo "✗ Kein TemplateSetting gefunden!\n";
    exit;
}

echo "✓ Record geladen (ID: {$record->id})\n\n";

// Teste, ob wir die Resource-Klasse laden können
$resourceClass = \App\Filament\Club\Resources\TemplateSettingResource::class;

echo "Checking Resource Configuration:\n";
echo "- Resource Class: $resourceClass\n";
echo "- Model: " . $resourceClass::getModel() . "\n";
echo "- Navigation Label: " . $resourceClass::getNavigationLabel() . "\n\n";

// Teste die Edit Page
$editPageClass = \App\Filament\Club\Resources\TemplateSettingResource\Pages\EditTemplateSetting::class;
echo "Edit Page Class: $editPageClass\n";

// Prüfe, ob getFormActions definiert ist
$reflection = new ReflectionClass($editPageClass);
if ($reflection->hasMethod('getFormActions')) {
    echo "✓ getFormActions() ist definiert\n";
} else {
    echo "✗ getFormActions() fehlt!\n";
}

// Teste FormSchema
echo "\n=== FORM SCHEMA ANALYSE ===\n\n";

try {
    // Erstelle ein Mock-Schema ohne Livewire-Kontext
    $schema = \Filament\Schemas\Schema::make();

    // Hole die Form-Definition
    $formSchema = $resourceClass::form($schema);

    echo "Schema erstellt, aber kann nicht ohne Livewire-Kontext gerendert werden.\n";
    echo "Das ist NORMAL - Filament benötigt einen Livewire-Component.\n\n";

} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage() . "\n\n";
}

// Wichtige Checks
echo "=== WICHTIGE ÜBERPRÜFUNGEN ===\n\n";

// 1. Prüfe Panel-Registrierung
echo "1. Panel-Registrierung:\n";
try {
    $panel = \Filament\Facades\Filament::getPanel('club');
    echo "   ✓ Club Panel ist registriert\n";
    echo "   Path: " . $panel->getPath() . "\n";

    $resources = $panel->getResources();
    echo "   Registered Resources: " . count($resources) . "\n";

    if (in_array($resourceClass, $resources)) {
        echo "   ✓ TemplateSettingResource ist im Panel registriert\n";
    } else {
        echo "   ✗ TemplateSettingResource ist NICHT im Panel registriert!\n";
        echo "   Dies könnte das Problem sein!\n";
    }
} catch (Exception $e) {
    echo "   ✗ Fehler: " . $e->getMessage() . "\n";
}

echo "\n2. Livewire-Komponenten:\n";
$livewireComponents = [
    'filament.club.resources.template-setting-resource.pages.edit-template-setting',
];

foreach ($livewireComponents as $component) {
    if (class_exists(\Livewire\Livewire::class)) {
        echo "   Livewire ist installiert\n";
        break;
    }
}

echo "\n=== MÖGLICHE PROBLEME ===\n\n";

echo "Wenn die Seite lädt, aber Felder nicht funktionieren:\n\n";

echo "1. JavaScript-Fehler im Browser\n";
echo "   → Öffnen Sie F12 → Console und suchen nach Fehlern\n\n";

echo "2. Livewire-Scripts nicht geladen\n";
echo "   → Im Browser: View Page Source → Suche nach '@livewireScripts'\n\n";

echo "3. Alpine.js nicht geladen\n";
echo "   → Console: Tippe 'Alpine' ein → sollte Object sein, nicht undefined\n\n";

echo "4. Form-Components werden nicht gerendert\n";
echo "   → F12 → Elements → Suche nach 'input' tags\n";
echo "   → Wenn keine inputs vorhanden: Problem im Schema\n\n";

echo "=== NÄCHSTER SCHRITT ===\n\n";
echo "Bitte machen Sie einen Screenshot der Seite und sagen Sie mir:\n";
echo "1. Sehen Sie überhaupt ein Formular?\n";
echo "2. Sehen Sie die Section-Überschriften (Grundeinstellungen, Farben, etc.)?\n";
echo "3. Sind die Eingabefelder sichtbar aber ausgegraut?\n";
echo "4. Oder ist die Seite komplett leer?\n";

echo "\n=== TEST ABGESCHLOSSEN ===\n";
