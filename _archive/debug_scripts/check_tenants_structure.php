<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "📋 TENANTS TABLE STRUCTURE\n";
echo "==========================\n\n";

$columns = DB::select('DESCRIBE tenants');

foreach ($columns as $col) {
    echo "Field: {$col->Field}\n";
    echo "  Type: {$col->Type}\n";
    echo "  Null: {$col->Null}\n";
    echo "  Default: " . ($col->Default ?? 'NULL') . "\n";
    echo "  Extra: {$col->Extra}\n\n";
}

echo "\n📊 SAMPLE TENANT DATA\n";
echo "=====================\n\n";

$tenant = DB::table('tenants')->first();
if ($tenant) {
    foreach ($tenant as $key => $value) {
        echo "{$key}: " . ($value ?? 'NULL') . "\n";
    }
}
