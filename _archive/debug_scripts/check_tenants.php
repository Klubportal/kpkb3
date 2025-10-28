<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;

echo "📋 Existierende Tenants:\n";
echo "========================\n";

$tenants = Tenant::with('domains')->get();

if ($tenants->count() > 0) {
    foreach ($tenants as $tenant) {
        echo "🏢 Tenant: {$tenant->id}\n";
        echo "   Name: {$tenant->name}\n";
        echo "   Email: {$tenant->email}\n";
        echo "   DB Name: tenant_{$tenant->id}\n";
        echo "   Domains: " . $tenant->domains->pluck('domain')->join(', ') . "\n";
        echo "   Erstellt: {$tenant->created_at}\n";
        echo "   ---------------------------------\n";
    }
} else {
    echo "❌ Keine Tenants gefunden!\n";
}

echo "\n📊 Gesamt: {$tenants->count()} Tenants\n";
