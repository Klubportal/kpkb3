<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking club_users table schema:\n\n";

$result = DB::select("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'club_users'
    AND TABLE_SCHEMA = DATABASE()");

foreach ($result as $col) {
    $nullable = $col->IS_NULLABLE === 'YES' ? '(nullable)' : '(NOT NULL)';
    echo "$col->COLUMN_NAME: $col->COLUMN_TYPE $nullable\n";
}

echo "\n\nChecking club_id column specifically:\n";
$colResult = DB::select("SELECT COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'club_users'
    AND COLUMN_NAME = 'club_id'
    AND TABLE_SCHEMA = DATABASE()");

if (count($colResult) > 0) {
    $colDetail = $colResult[0];
    echo "Type: $colDetail->COLUMN_TYPE\n";
    echo "Max Length: " . ($colDetail->CHARACTER_MAXIMUM_LENGTH ?? 'N/A') . "\n";
}

echo "\n\nSample tenant IDs:\n";
$tenants = DB::table('tenants')->select('id')->limit(3)->get();
foreach ($tenants as $t) {
    echo "ID: $t->id (length: " . strlen($t->id) . ")\n";
}
