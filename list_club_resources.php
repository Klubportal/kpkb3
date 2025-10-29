<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Initialize tenant
tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== CLUB PANEL RESOURCES OVERVIEW ===\n\n";

$resourceFiles = glob(__DIR__.'/app/Filament/Club/Resources/*Resource.php');

foreach ($resourceFiles as $file) {
    $className = basename($file, '.php');
    $fullClass = "App\\Filament\\Club\\Resources\\$className";

    if (!class_exists($fullClass)) {
        echo "✗ Class not found: $fullClass\n";
        continue;
    }

    $reflection = new ReflectionClass($fullClass);

    echo "Resource: $className\n";
    echo "  Full Class: $fullClass\n";

    // Get navigation properties
    $props = ['navigationLabel', 'navigationGroup', 'navigationSort', 'navigationIcon'];

    foreach ($props as $propName) {
        try {
            $prop = $reflection->getProperty($propName);
            $prop->setAccessible(true);
            $value = $prop->getValue();

            if (is_object($value)) {
                $value = get_class($value);
            }

            echo sprintf("  %-18s = %s\n", '$'.$propName, var_export($value, true));
        } catch (ReflectionException $e) {
            echo sprintf("  %-18s = NOT DEFINED\n", '$'.$propName);
        }
    }

    echo "\n";
}

echo "=== END ===\n";
