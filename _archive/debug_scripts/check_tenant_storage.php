<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Storage;

echo "🔍 CHECKING TENANT STORAGE CONFIGURATION\n";
echo "========================================\n\n";

$tenant = Tenant::find('nknapijed');

if (!$tenant) {
    echo "❌ Tenant 'nknapijed' not found!\n";
    exit(1);
}

echo "Tenant: {$tenant->name}\n\n";

// Initialize tenancy
tenancy()->initialize($tenant);

echo "📁 STORAGE DISK CONFIGURATION\n";
echo "==============================\n";

// Check default disk
$defaultDisk = config('filesystems.default');
echo "Default disk: {$defaultDisk}\n\n";

// Check available disks
$disks = config('filesystems.disks');
echo "Available disks:\n";
foreach ($disks as $name => $config) {
    echo "  - {$name}\n";
    if (isset($config['driver'])) {
        echo "    Driver: {$config['driver']}\n";
    }
    if (isset($config['root'])) {
        echo "    Root: {$config['root']}\n";
    }
    echo "\n";
}

// Test public disk
echo "📂 PUBLIC DISK TEST\n";
echo "===================\n";

try {
    $publicDisk = Storage::disk('public');

    // Check if disk exists
    echo "Public disk exists: ✅\n";

    // Get root path
    $rootPath = $publicDisk->path('');
    echo "Root path: {$rootPath}\n";

    // Check if root exists
    if (file_exists($rootPath)) {
        echo "Root exists: ✅\n";
    } else {
        echo "Root exists: ❌\n";
    }

    // Check logos directory
    $logosPath = $publicDisk->path('logos');
    echo "Logos path: {$logosPath}\n";

    if (file_exists($logosPath)) {
        echo "Logos directory exists: ✅\n";

        // List files
        $files = Storage::disk('public')->files('logos');
        echo "Files in logos: " . count($files) . "\n";
        foreach ($files as $file) {
            echo "  - {$file}\n";
        }
    } else {
        echo "Logos directory exists: ❌\n";
        echo "Attempting to create...\n";

        if (!is_dir($rootPath)) {
            mkdir($rootPath, 0755, true);
            echo "Created root directory\n";
        }

        Storage::disk('public')->makeDirectory('logos');
        echo "Created logos directory\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";

// Check symlinks
echo "🔗 SYMLINK CHECK\n";
echo "================\n";

$publicPath = public_path('storage');
echo "Public symlink path: {$publicPath}\n";

if (is_link($publicPath)) {
    echo "Symlink exists: ✅\n";
    $target = readlink($publicPath);
    echo "Symlink target: {$target}\n";
} elseif (file_exists($publicPath)) {
    echo "Path exists but is not a symlink: ⚠️\n";
} else {
    echo "Symlink does not exist: ❌\n";
}

echo "\n";

// Check Filament configuration
echo "⚙️ FILAMENT CONFIGURATION\n";
echo "=========================\n";

$filamentDefaultDisk = config('filament.default_filesystem_disk');
echo "Filament default disk: " . ($filamentDefaultDisk ?? 'null (uses default)') . "\n";

tenancy()->end();

echo "\n✅ Check complete!\n";
