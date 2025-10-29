<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Initialize tenant
tenancy()->initialize(tenancy()->find('nknapijed'));

echo "=== TEMPLATE_SETTINGS TABLE STRUCTURE ===\n\n";
$columns = DB::select('DESCRIBE template_settings');
foreach($columns as $col) {
    echo sprintf(
        "%-30s | %-20s | %-10s | Default: %s\n",
        $col->Field,
        $col->Type,
        $col->Null == 'YES' ? 'NULL' : 'NOT NULL',
        $col->Default ?? 'NULL'
    );
}

echo "\n=== CURRENT TEMPLATE_SETTINGS VALUES (ID=1) ===\n\n";
$setting = DB::table('template_settings')->find(1);
if($setting) {
    foreach($setting as $key => $value) {
        if(strlen($value) > 100) {
            $value = substr($value, 0, 100) . '... [truncated]';
        }
        echo sprintf("%-30s = %s\n", $key, $value ?? 'NULL');
    }
} else {
    echo "No record found with ID=1\n";
}

echo "\n=== CHECKING FOR COLOR FIELDS ===\n\n";
$colorFields = ['primary_color', 'secondary_color', 'accent_color', 'text_color',
                'header_bg_color', 'header_text_color', 'footer_bg_color', 'footer_text_color'];

foreach($colorFields as $field) {
    $exists = collect($columns)->where('Field', $field)->isNotEmpty();
    echo sprintf("%-30s : %s\n", $field, $exists ? '✓ EXISTS' : '✗ MISSING');
}

echo "\n=== CHECKING FOR LOGO FIELDS ===\n\n";
$logoFields = ['logo_path', 'logo_height', 'logo_position', 'show_club_name'];

foreach($logoFields as $field) {
    $exists = collect($columns)->where('Field', $field)->isNotEmpty();
    echo sprintf("%-30s : %s\n", $field, $exists ? '✓ EXISTS' : '✗ MISSING');
}
