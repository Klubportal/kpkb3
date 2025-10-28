<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🔍 DEEP SEARCH FOR NK PRIGORJE (598) - 11 COMPETITIONS\n";
echo str_repeat("═", 80) . "\n\n";

try {
    // Check if there's data in other tables
    echo "📊 Step 1: Check all clubs in kp_server to find correct club ID\n";
    echo str_repeat("─", 80) . "\n\n";

    $clubs = DB::connection('mysql')
        ->table('kp_server.clubs')
        ->select('*')
        ->limit(5)
        ->get();

    if (count($clubs) > 0) {
        echo "First 5 clubs in kp_server.clubs:\n";
        foreach ($clubs as $club) {
            echo json_encode($club, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        }
    } else {
        echo "❌ No clubs table data\n\n";
    }

    // Check competitions table structure
    echo str_repeat("═", 80) . "\n";
    echo "📊 Step 2: Check competitions table in kp_server\n";
    echo str_repeat("─", 80) . "\n\n";

    $columns = DB::connection('mysql')->select("
        SELECT COLUMN_NAME, COLUMN_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'kp_server'
        AND TABLE_NAME = 'competitions'
    ");

    echo "Columns in competitions table:\n";
    foreach ($columns as $col) {
        echo "  - " . $col->COLUMN_NAME . " (" . $col->COLUMN_TYPE . ")\n";
    }
    echo "\n";

    // Check all records for club 598
    echo str_repeat("═", 80) . "\n";
    echo "📊 Step 3: Show FULL records for club_fifa_id 598\n";
    echo str_repeat("─", 80) . "\n\n";

    $fullRecords = DB::connection('mysql')
        ->table('kp_server.competitions')
        ->where('club_fifa_id', '598')
        ->get();

    echo "Total records: " . count($fullRecords) . "\n\n";

    foreach ($fullRecords as $i => $rec) {
        echo "【" . ($i + 1) . "】\n";
        echo json_encode($rec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }

    // Try looking in 'matches' table
    echo str_repeat("═", 80) . "\n";
    echo "📊 Step 4: Check if competitions info is in matches table\n";
    echo str_repeat("─", 80) . "\n\n";

    $matchesCount = DB::connection('mysql')
        ->table('kp_server.matches')
        ->count();

    echo "Total matches in kp_server.matches: " . $matchesCount . "\n\n";

    if ($matchesCount > 0) {
        $sampleMatches = DB::connection('mysql')
            ->table('kp_server.matches')
            ->limit(3)
            ->get();

        echo "Sample match structure:\n";
        foreach ($sampleMatches as $match) {
            echo json_encode($match, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        }
    }

    // Look for all data in current tenant DB
    echo str_repeat("═", 80) . "\n";
    echo "📊 Step 5: Check kp_club_management.competitions table\n";
    echo str_repeat("─", 80) . "\n\n";

    $tenantComps = DB::connection('mysql')
        ->table('kp_club_management.competitions')
        ->limit(10)
        ->get();

    echo "Records in kp_club_management.competitions: " . count($tenantComps) . "\n";
    if (count($tenantComps) > 0) {
        echo "\nFirst record:\n";
        echo json_encode($tenantComps[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n";

    echo str_repeat("═", 80) . "\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
