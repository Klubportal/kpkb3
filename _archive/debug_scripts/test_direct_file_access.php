<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$host = 'nknapijed.localhost';
$domain = explode('.', $host)[0];

echo "Domain: $domain\n";

$tenant = \App\Models\Tenant::find($domain);

if (!$tenant) {
    die("Tenant not found\n");
}

echo "Tenant ID: {$tenant->id}\n";

$path = 'logos/01K8KSS023SW8CYGG1QM6G5GZG.png';
$storagePath = storage_path("tenant{$tenant->id}/app/public/{$path}");

echo "Storage path: $storagePath\n";
echo "File exists: " . (file_exists($storagePath) ? 'YES' : 'NO') . "\n";

if (file_exists($storagePath)) {
    echo "File size: " . number_format(filesize($storagePath) / 1024, 2) . " KB\n";
    echo "Reading file...\n";
    $content = file_get_contents($storagePath);
    echo "Read " . number_format(strlen($content) / 1024, 2) . " KB successfully\n";
}
