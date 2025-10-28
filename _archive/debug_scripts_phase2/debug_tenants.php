<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking tenants table IDs:\n\n";

$tenants = DB::table('tenants')->limit(1)->get();

foreach ($tenants as $t) {
    echo "Tenant ID: " . $t->id . "\n";
    echo "ID Type: " . gettype($t->id) . "\n";
    echo "ID Length: " . strlen((string)$t->id) . "\n";
    echo "Club Name: " . $t->club_name . "\n";
}

echo "\n\nAll available columns in tenants:\n";
$columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'tenants' AND TABLE_SCHEMA = DATABASE() ORDER BY ORDINAL_POSITION");

foreach ($columns as $col) {
    echo "  - " . $col->COLUMN_NAME . "\n";
}
