<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Table existence check:\n\n";
echo "club_members: " . (Schema::hasTable('club_members') ? "✅ EXISTS\n" : "❌ NOT FOUND\n");
echo "club_users: " . (Schema::hasTable('club_users') ? "✅ EXISTS\n" : "❌ NOT FOUND\n");

if (!Schema::hasTable('club_members')) {
    echo "\n⚠️  club_members table is missing! The ClubMember model expects it.\n";
    echo "\nLet's check what table has the right structure for UUIDs...\n";

    // Check all tables
    $tables = DB::select('SHOW TABLES');
    echo "\nAll tables:\n";
    foreach ($tables as $t) {
        $name = array_values((array)$t)[0];
        echo "  - $name\n";
    }
}

echo "\nClubMember model table: " . (new \App\Models\ClubMember)->getTable() . "\n";

// Check if Club model is based on tenants (UUIDs)
echo "Club model table: " . (new \App\Models\Club)->getTable() . "\n";

// Check Club ID type
if (Schema::hasTable('tenants')) {
    $columns = DB::select("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'tenants' AND COLUMN_NAME = 'id'");
    if (count($columns) > 0) {
        echo "Tenants 'id' column type: " . $columns[0]->COLUMN_TYPE . "\n";
    }
}

if (Schema::hasTable('club_members')) {
    $columns = DB::select("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'club_members' AND COLUMN_NAME = 'club_id'");
    if (count($columns) > 0) {
        echo "club_members 'club_id' column type: " . $columns[0]->COLUMN_TYPE . "\n";
    }
}
