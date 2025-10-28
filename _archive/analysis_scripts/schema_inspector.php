<?php
/**
 * Database Schema Inspector
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "📊 Database Schema Inspector\n";
echo str_repeat("=", 60) . "\n\n";

// Get all tables
$tables = DB::select('SHOW TABLES');
$tableNames = [];
foreach ($tables as $table) {
    $tableNames[] = array_values((array)$table)[0];
}

echo "📋 Tables in Database (" . count($tableNames) . "):\n";
foreach ($tableNames as $table) {
    echo "   - $table\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔍 Critical Tables Status:\n\n";

$criticalTables = ['users', 'tenants', 'domains', 'user_club', 'contact_form_submissions'];

foreach ($criticalTables as $table) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        echo "   ✅ $table: {$count} records\n";

        // Show columns
        $columns = Schema::getColumnListing($table);
        echo "      Columns: " . implode(', ', array_slice($columns, 0, 5));
        if (count($columns) > 5) {
            echo ", ... (" . (count($columns) - 5) . " more)";
        }
        echo "\n";
    } else {
        echo "   ❌ $table: NOT FOUND\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "⚠️  Issues Detected:\n\n";

$issues = [];

if (!Schema::hasTable('user_club')) {
    $issues[] = "Missing 'user_club' table for user-club relationships";
}

if (Schema::hasTable('users') && Schema::hasTable('tenants')) {
    $usersCount = DB::table('users')->count();
    $tenantsCount = DB::table('tenants')->count();

    if ($usersCount > 0 && $tenantsCount > 0) {
        if (Schema::hasTable('user_club')) {
            $assignmentCount = DB::table('user_club')->count();
            if ($assignmentCount === 0) {
                $issues[] = "No user-club assignments found (but table exists)";
            }
        }
    }
}

if (count($issues) === 0) {
    echo "   ✅ No critical issues detected!\n";
} else {
    foreach ($issues as $i => $issue) {
        echo "   " . ($i + 1) . ". ⚠️  " . $issue . "\n";
    }
}

echo "\n";
