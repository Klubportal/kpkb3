<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking logo settings for tenant nkprigorjem...\n\n";

try {
    tenancy()->initialize('nkprigorjem');

    $settings = \App\Models\Tenant\TemplateSetting::current();

    echo "Template Settings:\n";
    echo "  Website Name: " . ($settings->website_name ?? 'NULL') . "\n";
    echo "  Logo: " . ($settings->logo ?? 'NULL') . "\n";
    echo "  Club FIFA ID: " . ($settings->club_fifa_id ?? 'NULL') . "\n";

    if ($settings->logo) {
        $fullPath = storage_path('app/public/' . $settings->logo);
        echo "\nFull Path: " . $fullPath . "\n";
        echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

        $publicPath = public_path('storage/' . $settings->logo);
        echo "Public Path: " . $publicPath . "\n";
        echo "Public file exists: " . (file_exists($publicPath) ? 'YES' : 'NO') . "\n";
    }

    // Check storage link
    $storageLink = public_path('storage');
    echo "\nStorage link exists: " . (file_exists($storageLink) ? 'YES' : 'NO') . "\n";
    if (file_exists($storageLink)) {
        echo "Storage link points to: " . readlink($storageLink) . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
