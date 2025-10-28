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

echo "COMET tables in tenant_nknapijed:\n";
echo "=================================\n\n";

$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (str_starts_with($tableName, 'comet')) {
        echo "- {$tableName}\n";
    }
}
