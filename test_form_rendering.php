<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Initialize tenant
tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== TESTING TEMPLATE SETTING RESOURCE FORM ===\n\n";

// Test 1: Load the resource and render form
echo "1. Form Schema Test:\n";
try {
    $resourceClass = \App\Filament\Club\Resources\TemplateSettingResource::class;

    // Get the schema
    $schema = $resourceClass::schema(\Filament\Schemas\Schema::make());

    echo "   ✓ Schema loaded successfully\n";

    // Get components
    $components = $schema->getComponents();
    echo "   Total sections: " . count($components) . "\n\n";

    foreach ($components as $index => $component) {
        $componentClass = get_class($component);
        echo "   Section " . ($index + 1) . ": " . $componentClass . "\n";

        if (method_exists($component, 'getHeading')) {
            echo "      Heading: " . $component->getHeading() . "\n";
        }

        if (method_exists($component, 'getChildComponents')) {
            $children = $component->getChildComponents();
            echo "      Fields: " . count($children) . "\n";

            foreach ($children as $child) {
                $childClass = get_class($child);
                $fieldName = method_exists($child, 'getName') ? $child->getName() : 'unknown';
                echo "         - " . basename(str_replace('\\', '/', $childClass)) . ": " . $fieldName . "\n";
            }
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace:\n" . $e->getTraceAsString() . "\n";
}

// Test 2: Check if ColorPicker is properly imported
echo "\n2. Component Imports Check:\n";
$reflection = new ReflectionClass(\App\Filament\Club\Resources\TemplateSettingResource::class);
$fileContent = file_get_contents($reflection->getFileName());

$components = [
    'ColorPicker' => 'Filament\Forms\Components\ColorPicker',
    'FileUpload' => 'Filament\Forms\Components\FileUpload',
    'TextInput' => 'Filament\Forms\Components\TextInput',
    'Toggle' => 'Filament\Forms\Components\Toggle',
    'Section' => 'Filament\Schemas\Components\Section',
];

foreach ($components as $name => $namespace) {
    $found = strpos($fileContent, "use $namespace;") !== false;
    echo "   " . ($found ? '✓' : '✗') . " $name ($namespace)\n";
}

echo "\n=== TEST COMPLETE ===\n";
