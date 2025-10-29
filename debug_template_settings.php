<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEMPLATE SETTINGS DEBUG ===\n\n";

// Initialize tenant
$tenant = \Stancl\Tenancy\Facades\Tenancy::find('nknaprijed');
if (!$tenant) {
    die("Tenant 'nknaprijed' nicht gefunden!\n");
}

tenancy()->initialize($tenant);

echo "Tenant initialisiert: nknaprijed\n\n";

// Check if template_settings record exists
$setting = \App\Models\Tenant\TemplateSetting::first();

if (!$setting) {
    echo "❌ KEINE template_settings gefunden!\n";
    echo "Erstelle Default-Eintrag...\n\n";

    $setting = \App\Models\Tenant\TemplateSetting::create([
        'website_name' => 'NK Naprijed',
        'club_fifa_id' => 396,
        'primary_color' => '#DC052D',
        'secondary_color' => '#0066B2',
        'accent_color' => '#FCBF49',
    ]);

    echo "✓ Template Settings erstellt mit ID: {$setting->id}\n\n";
}

echo "=== TEMPLATE SETTING ===\n";
echo "ID: {$setting->id}\n";
echo "Website Name: {$setting->website_name}\n";
echo "Club FIFA ID: {$setting->club_fifa_id}\n";
echo "Primary Color: {$setting->primary_color}\n\n";

echo "=== ALLE FELDER ===\n";
foreach ($setting->getAttributes() as $key => $value) {
    echo sprintf("%-30s: %s\n", $key, $value ?? '(null)');
}

echo "\n=== FILLABLE FIELDS ===\n";
print_r($setting->getFillable());

echo "\n\n=== RESOURCE CHECK ===\n";
$resourceClass = \App\Filament\Club\Resources\TemplateSettingResource::class;
echo "Resource exists: " . (class_exists($resourceClass) ? "✓" : "✗") . "\n";

if (class_exists($resourceClass)) {
    echo "Model: " . $resourceClass::getModel() . "\n";
    echo "Navigation Label: " . $resourceClass::getNavigationLabel() . "\n";

    // Try to get form schema
    try {
        $schema = new \Filament\Schemas\Schema();
        $formSchema = $resourceClass::form($schema);
        echo "Form Schema: ✓ exists\n";
        echo "Components count: " . count($formSchema->getComponents()) . "\n";
    } catch (\Exception $e) {
        echo "Form Schema Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== DONE ===\n";
