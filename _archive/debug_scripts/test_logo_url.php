<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Storage;

echo "🧪 TESTING LOGO URL ACCESS\n";
echo "==========================\n\n";

$tenant = Tenant::find('nknapijed');

if (!$tenant) {
    echo "❌ Tenant not found\n";
    exit(1);
}

tenancy()->initialize($tenant);

$logoPath = 'logos/01K8KSS023SW8CYGG1QM6G5GZG.png';

echo "Logo path in DB: {$logoPath}\n\n";

// Check if file exists
$disk = Storage::disk('public');
if (!$disk->exists($logoPath)) {
    echo "❌ File does NOT exist in storage\n";
    tenancy()->end();
    exit(1);
}

echo "✅ File exists\n";
echo "Size: " . number_format($disk->size($logoPath) / 1024, 2) . " KB\n";
echo "MIME type: " . $disk->getMimeType($logoPath) . "\n\n";

// Full file path
$fullPath = $disk->path($logoPath);
echo "Full path: {$fullPath}\n";
echo "File readable: " . (is_readable($fullPath) ? 'YES' : 'NO') . "\n\n";

// Test the route
echo "🔗 TESTING ROUTE:\n";
echo "=================\n";

$url = "http://nknapijed.localhost:8000/storage/{$logoPath}";
echo "URL: {$url}\n\n";

// Simulate the route logic
echo "Testing route logic...\n";

$path = $logoPath;
$allowedPaths = ['logos', 'branding', 'media', 'uploads'];
$firstSegment = explode('/', $path)[0];

echo "First segment: {$firstSegment}\n";

if (!in_array($firstSegment, $allowedPaths)) {
    echo "❌ Path NOT allowed\n";
} else {
    echo "✅ Path is allowed\n";
}

if ($disk->exists($path)) {
    echo "✅ File exists for route\n";
    $file = $disk->get($path);
    echo "✅ File can be read (" . strlen($file) . " bytes)\n";
    $mimeType = $disk->getMimeType($path);
    echo "MIME type: {$mimeType}\n";
} else {
    echo "❌ File NOT found for route\n";
}

tenancy()->end();

echo "\n";
echo "✅ Test complete!\n\n";
echo "📌 TO VIEW THE LOGO:\n";
echo "1. Open your browser\n";
echo "2. Go to: {$url}\n";
echo "3. You should see the uploaded logo\n\n";

echo "📌 LOGO IN TEMPLATE:\n";
echo "The logo should appear in:\n";
echo "- Frontend header: http://nknapijed.localhost:8000/\n";
echo "- Admin panel: http://nknapijed.localhost:8000/club/template-settings/1/edit\n";
