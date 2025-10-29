<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Initialize tenant
tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== CHECKING FORM FIELD FILLABILITY ===\n\n";

// Test 1: Check Model
echo "1. TemplateSetting Model Check:\n";
$model = \App\Models\Tenant\TemplateSetting::first();
if ($model) {
    echo "   ✓ Model found (ID: {$model->id})\n";

    // Check fillable
    $fillable = $model->getFillable();
    echo "   Fillable fields: " . count($fillable) . "\n";

    if (empty($fillable)) {
        echo "   ⚠ WARNING: No fillable fields defined!\n";
        echo "   This means mass assignment won't work!\n\n";

        // Check if guarded is empty
        $guarded = $model->getGuarded();
        echo "   Guarded fields: " . implode(', ', $guarded) . "\n";

        if (in_array('*', $guarded)) {
            echo "   ✗ PROBLEM: Model is guarded with ['*'] - all fields protected!\n";
        }
    } else {
        echo "   Fillable: " . implode(', ', array_slice($fillable, 0, 10)) . "...\n";
    }

    // Try to update a field
    echo "\n2. Testing Update:\n";
    $original = $model->primary_color;
    echo "   Original primary_color: $original\n";

    try {
        $model->update(['primary_color' => '#FF0000']);
        $model->refresh();
        echo "   ✓ Update successful! New value: {$model->primary_color}\n";

        // Revert
        $model->update(['primary_color' => $original]);
        echo "   ✓ Reverted to original\n";
    } catch (Exception $e) {
        echo "   ✗ Update failed: " . $e->getMessage() . "\n";
    }

} else {
    echo "   ✗ No TemplateSetting model found\n";
}

// Test 3: Check table structure
echo "\n3. Database Fields Check:\n";
$columns = DB::select("SHOW COLUMNS FROM template_settings");
$colorFields = ['primary_color', 'secondary_color', 'accent_color', 'text_color',
                'header_bg_color', 'header_text_color', 'footer_bg_color', 'footer_text_color',
                'badge_bg_color', 'badge_text_color', 'hero_bg_color', 'hero_text_color'];

foreach ($colorFields as $field) {
    $exists = collect($columns)->where('Field', $field)->isNotEmpty();
    $value = $exists ? $model->$field : 'N/A';
    echo sprintf("   %-25s : %s (Value: %s)\n", $field, $exists ? '✓' : '✗', $value);
}

echo "\n4. Logo Field Check:\n";
echo "   logo field exists: " . (collect($columns)->where('Field', 'logo')->isNotEmpty() ? '✓' : '✗') . "\n";
echo "   Current logo value: " . ($model->logo ?? 'NULL') . "\n";

echo "\n=== CHECK COMPLETE ===\n";
