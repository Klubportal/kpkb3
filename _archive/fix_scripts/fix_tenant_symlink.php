<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\File;

echo "🔗 FIXING TENANT STORAGE SYMLINKS\n";
echo "==================================\n\n";

$tenant = Tenant::find('nknapijed');

if (!$tenant) {
    echo "❌ Tenant 'nknapijed' not found!\n";
    exit(1);
}

echo "Tenant: {$tenant->name}\n\n";

// Initialize tenancy
tenancy()->initialize($tenant);

// Get paths
$tenantStoragePath = storage_path('app/public');
$publicStoragePath = public_path('storage');

echo "Tenant storage path: {$tenantStoragePath}\n";
echo "Public storage path: {$publicStoragePath}\n\n";

// Check if tenant storage exists
if (!file_exists($tenantStoragePath)) {
    echo "Creating tenant storage directory...\n";
    File::makeDirectory($tenantStoragePath, 0755, true);
    echo "✅ Created\n\n";
} else {
    echo "✅ Tenant storage directory exists\n\n";
}

// Create logos directory if it doesn't exist
$logosPath = $tenantStoragePath . '/logos';
if (!file_exists($logosPath)) {
    echo "Creating logos directory...\n";
    File::makeDirectory($logosPath, 0755, true);
    echo "✅ Created logos directory\n\n";
} else {
    echo "✅ Logos directory exists\n\n";
}

// Check public/storage symlink
echo "Checking public/storage symlink...\n";

if (file_exists($publicStoragePath)) {
    if (is_link($publicStoragePath)) {
        echo "Current symlink target: " . readlink($publicStoragePath) . "\n";

        // Remove old symlink
        echo "Removing old symlink...\n";
        unlink($publicStoragePath);
    } else {
        echo "⚠️ Path exists but is not a symlink, removing...\n";

        if (is_dir($publicStoragePath)) {
            File::deleteDirectory($publicStoragePath);
            echo "✅ Removed directory\n";
        } else {
            unlink($publicStoragePath);
            echo "✅ Removed file\n";
        }
    }
}

// Create new symlink
echo "Creating tenant-specific symlink...\n";

// On Windows, we need to use the actual path
if (PHP_OS_FAMILY === 'Windows') {
    // Convert to Windows path
    $target = str_replace('/', '\\', $tenantStoragePath);
    $link = str_replace('/', '\\', $publicStoragePath);

    echo "Target: {$target}\n";
    echo "Link: {$link}\n";

    // Create symlink using Windows command
    $command = "mklink /D \"{$link}\" \"{$target}\"";
    echo "Command: {$command}\n";

    exec($command, $output, $returnCode);

    if ($returnCode === 0) {
        echo "✅ Symlink created successfully!\n";
    } else {
        echo "❌ Failed to create symlink\n";
        echo "Output: " . implode("\n", $output) . "\n";
        echo "Return code: {$returnCode}\n";

        // Try using symlink function
        echo "\nTrying PHP symlink function...\n";
        if (symlink($target, $link)) {
            echo "✅ Symlink created using PHP function!\n";
        } else {
            echo "❌ Failed with PHP function too\n";
        }
    }
} else {
    // Unix/Linux
    if (symlink($tenantStoragePath, $publicStoragePath)) {
        echo "✅ Symlink created successfully!\n";
    } else {
        echo "❌ Failed to create symlink\n";
    }
}

echo "\n";

// Verify symlink
if (is_link($publicStoragePath)) {
    $target = readlink($publicStoragePath);
    echo "✅ Symlink verified!\n";
    echo "   Target: {$target}\n";

    // Test if accessible
    $testPath = $publicStoragePath . '/logos';
    if (file_exists($testPath)) {
        echo "   ✅ Logos directory accessible via symlink\n";
    } else {
        echo "   ❌ Logos directory NOT accessible via symlink\n";
    }
} else {
    echo "❌ Symlink not created\n";
}

tenancy()->end();

echo "\n✅ Complete!\n";
echo "\nℹ️ NOTE: On Windows, you may need to run this script as Administrator\n";
echo "to create symbolic links. Alternatively, enable Developer Mode in Windows Settings.\n";
