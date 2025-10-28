<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "【 Check comet_players columns in tenant 】\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    $columns = Schema::getColumnListing('comet_players');

    $hasAgeCategory = in_array('primary_age_category', $columns);
    $hasBirthYear = in_array('birth_year', $columns);

    echo "primary_age_category: " . ($hasAgeCategory ? "✅ EXISTS" : "❌ MISSING") . "\n";
    echo "birth_year: " . ($hasBirthYear ? "✅ EXISTS" : "❌ MISSING") . "\n\n";

    if (!$hasAgeCategory || !$hasBirthYear) {
        echo "Need to run migration:\n";
        echo "database/migrations/2025_10_27_004556_add_age_category_to_comet_players_table.php\n";
    } else {
        echo "All fields exist! ✅\n";
    }
});
