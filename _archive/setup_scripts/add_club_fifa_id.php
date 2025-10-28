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

echo "Checking for club_fifa_id in template_settings...\n";
echo "===================================================\n\n";

$columns = DB::select('DESCRIBE template_settings');
$columnNames = array_map(function($col) {
    return $col->Field;
}, $columns);

if (in_array('club_fifa_id', $columnNames)) {
    echo "✓ club_fifa_id column exists\n";

    $settings = DB::table('template_settings')->first();
    echo "Current value: " . ($settings->club_fifa_id ?? 'NULL') . "\n";
} else {
    echo "✗ club_fifa_id column does NOT exist\n";
    echo "\nAdding club_fifa_id column...\n";

    DB::statement("ALTER TABLE template_settings ADD COLUMN `club_fifa_id` BIGINT NULL AFTER `website_name`");
    echo "✓ Column added\n";

    echo "\nSetting club_fifa_id to 396 for NK Naprijed...\n";
    DB::table('template_settings')->update(['club_fifa_id' => 396]);
    echo "✓ Set to 396\n";
}
