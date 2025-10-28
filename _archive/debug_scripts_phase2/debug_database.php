<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE AUDIT ===\n\n";

// Get all tables
$tables = DB::select('SHOW TABLES');
echo "📊 Tables in database:\n";
foreach ($tables as $t) {
    $tableName = array_values((array)$t)[0];
    $count = DB::table($tableName)->count();
    echo "  - $tableName: $count records\n";
}

echo "\n📋 Key Tables:\n";
echo "  Users: " . DB::table('users')->count() . "\n";
echo "  Clubs: " . DB::table('clubs')->count() . "\n";
echo "  Banners: " . DB::table('banners')->count() . "\n";
echo "  Sponsors: " . DB::table('sponsors')->count() . "\n";

echo "\n✅ Check Panels in bootstrap/providers.php:\n";
$providerContent = file_get_contents(__DIR__ . '/bootstrap/providers.php');
echo $providerContent;

?>
