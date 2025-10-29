<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Initialize tenant
tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== DATABASE CONNECTION & FIELD TEST ===\n\n";

// Test 1: Database Connection
echo "1. Database Connection:\n";
try {
    DB::connection('tenant')->getPdo();
    echo "   ✓ Tenant database connected\n";
    $dbName = DB::connection('tenant')->getDatabaseName();
    echo "   Database: $dbName\n";
} catch (Exception $e) {
    echo "   ✗ Connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Check template_settings record
echo "\n2. TemplateSetting Record:\n";
$setting = DB::table('template_settings')->first();
if ($setting) {
    echo "   ✓ Record found (ID: {$setting->id})\n";
    echo "   Website Name: {$setting->website_name}\n";
    echo "   Primary Color: {$setting->primary_color}\n";
} else {
    echo "   ✗ No record found\n";
}

// Test 3: Try to update using Model
echo "\n3. Testing Model Update:\n";
try {
    $model = \App\Models\Tenant\TemplateSetting::first();

    if (!$model) {
        echo "   ✗ No model found\n";
    } else {
        $oldColor = $model->primary_color;
        $newColor = '#TEST123';

        echo "   Old color: $oldColor\n";

        // Try update
        $model->primary_color = $newColor;
        $saved = $model->save();

        echo "   Save returned: " . ($saved ? 'true' : 'false') . "\n";

        // Refresh and check
        $model->refresh();
        echo "   New color after save: {$model->primary_color}\n";

        if ($model->primary_color === $newColor) {
            echo "   ✓ Update successful!\n";

            // Revert
            $model->primary_color = $oldColor;
            $model->save();
            echo "   ✓ Reverted to original\n";
        } else {
            echo "   ✗ Update failed - value not changed\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 4: Check if fields are writable
echo "\n4. Field Permissions Check:\n";
$model = \App\Models\Tenant\TemplateSetting::first();
$testFields = ['primary_color', 'website_name', 'logo', 'hero_bg_color'];

foreach ($testFields as $field) {
    $isFillable = in_array($field, $model->getFillable());
    echo sprintf("   %-20s : %s\n", $field, $isFillable ? '✓ Fillable' : '✗ Not fillable');
}

// Test 5: Check table structure
echo "\n5. Table Structure Check:\n";
$columns = DB::select("DESCRIBE template_settings");
echo "   Total columns: " . count($columns) . "\n";
echo "   Columns: " . implode(', ', array_column($columns, 'Field')) . "\n";

echo "\n=== TEST COMPLETE ===\n";
