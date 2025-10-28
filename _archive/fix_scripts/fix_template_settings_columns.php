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

echo "Current structure of template_settings:\n";
echo "========================================\n\n";

$columns = DB::select('DESCRIBE template_settings');
$existingColumns = [];
foreach ($columns as $column) {
    $existingColumns[] = $column->Field;
    echo "- {$column->Field} ({$column->Type})\n";
}

echo "\n\nAdding missing color columns...\n";
echo "=================================\n\n";

$colorColumns = [
    'header_text_color' => 'varchar(20)',
    'badge_bg_color' => 'varchar(20)',
    'badge_text_color' => 'varchar(20)',
    'footer_text_color' => 'varchar(20)',
];

foreach ($colorColumns as $column => $type) {
    if (!in_array($column, $existingColumns)) {
        echo "Adding column: {$column}...\n";
        DB::statement("ALTER TABLE template_settings ADD COLUMN `{$column}` {$type} NULL AFTER `updated_at`");
        echo "✓ {$column} added\n";
    } else {
        echo "✓ {$column} already exists\n";
    }
}

echo "\n✓ All required columns are now present!\n";
