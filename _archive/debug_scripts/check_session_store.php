<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Attempting to resolve session.store...\n";
    $store = app('session.store');
    echo 'Resolved session store class: '.get_class($store)."\n";
} catch (\Exception $e) {
    echo "ERROR: " . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
