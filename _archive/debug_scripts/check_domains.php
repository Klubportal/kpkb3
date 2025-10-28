<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Domains in DB ===\n\n";

$domains = \Stancl\Tenancy\Database\Models\Domain::all();

foreach ($domains as $domain) {
    echo "Domain: {$domain->domain} -> Tenant: {$domain->tenant_id}\n";
}

echo "\n=== Expected Domain ===\n";
echo "nknapijed.localhost:8000\n";
