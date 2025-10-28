<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n╔════════════════════════════════════════════════════════╗\n";
echo "║  TENANT TABLE STRUCTURE CHECK                          ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Initialize tenant context
tenancy()->initialize('testclub');

echo "📊 PLAYERS TABLE (tenant_testclub):\n";
echo "══════════════════════════════════════════════════════════\n";

$columns = DB::select('SHOW COLUMNS FROM players');

foreach ($columns as $col) {
    echo sprintf("  %-25s %s\n", $col->Field, $col->Type);
}

echo "\n";
echo "══════════════════════════════════════════════════════════\n\n";

echo "📊 MATCHES TABLE (tenant_testclub):\n";
echo "══════════════════════════════════════════════════════════\n";

$columns = DB::select('SHOW COLUMNS FROM matches');

foreach ($columns as $col) {
    echo sprintf("  %-25s %s\n", $col->Field, $col->Type);
}

echo "\n";
echo "══════════════════════════════════════════════════════════\n\n";

echo "📊 NEWS TABLE (tenant_testclub):\n";
echo "══════════════════════════════════════════════════════════\n";

$columns = DB::select('SHOW COLUMNS FROM news');

foreach ($columns as $col) {
    echo sprintf("  %-25s %s\n", $col->Field, $col->Type);
}

echo "\n";
echo "══════════════════════════════════════════════════════════\n\n";

echo "📊 EVENTS TABLE (tenant_testclub):\n";
echo "══════════════════════════════════════════════════════════\n";

$columns = DB::select('SHOW COLUMNS FROM events');

foreach ($columns as $col) {
    echo sprintf("  %-25s %s\n", $col->Field, $col->Type);
}

echo "\n";
