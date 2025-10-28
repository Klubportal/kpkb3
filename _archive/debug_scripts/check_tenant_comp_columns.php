<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "【 Tenant comet_competitions columns 】\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    $columns = Schema::getColumnListing('comet_competitions');

    foreach ($columns as $col) {
        echo "• {$col}\n";
    }
});
