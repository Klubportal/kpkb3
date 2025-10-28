<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
$domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', 'nknapijed.localhost')->first();
if ($domain) {
    tenancy()->initialize($domain->tenant);
}

echo "Adding Hero color columns to template_settings...\n";
echo "===================================================\n\n";

$columns = DB::select('DESCRIBE template_settings');
$existingColumns = array_map(function($col) {
    return $col->Field;
}, $columns);

$heroColumns = [
    'hero_bg_color' => 'varchar(20)',
    'hero_text_color' => 'varchar(20)',
];

foreach ($heroColumns as $column => $type) {
    if (!in_array($column, $existingColumns)) {
        echo "Adding column: {$column}...\n";
        DB::statement("ALTER TABLE template_settings ADD COLUMN `{$column}` {$type} NULL AFTER `header_text_color`");
        echo "✓ {$column} added\n";
    } else {
        echo "✓ {$column} already exists\n";
    }
}

echo "\n✓ Hero color columns are now present!\n";
