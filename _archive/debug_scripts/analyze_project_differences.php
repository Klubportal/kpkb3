<?php

echo "🔍 ANALYZING PROJECT DIFFERENCES\n";
echo "===============================\n\n";

$source = 'c:\xampp\htdocs\kp_club_management';
$target = 'c:\xampp\htdocs\kpkb3';

echo "Source: $source\n";
echo "Target: $target\n\n";

// Define what to analyze
$directories = [
    'app/Models' => 'Models (neue Models)',
    'app/Http/Controllers' => 'Controllers',
    'app/Filament' => 'Filament Resources',
    'app/Jobs' => 'Jobs',
    'app/Listeners' => 'Listeners',
    'app/Providers' => 'Providers',
    'database/migrations' => 'Migrations',
    'database/seeders' => 'Seeders',
    'resources/views' => 'Views',
    'resources/js' => 'JavaScript',
    'resources/css' => 'CSS',
    'routes' => 'Routes',
    'config' => 'Config files',
    'public' => 'Public assets'
];

echo "📊 DIRECTORY ANALYSIS\n";
echo "=====================\n";

foreach ($directories as $dir => $description) {
    $sourcePath = "$source\\$dir";
    $targetPath = "$target\\$dir";

    $sourceExists = is_dir($sourcePath);
    $targetExists = is_dir($targetPath);

    echo sprintf(
        "%-25s | Source: %s | Target: %s | %s\n",
        $dir,
        $sourceExists ? '✅' : '❌',
        $targetExists ? '✅' : '❌',
        $description
    );

    if ($sourceExists) {
        $sourceFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $sourceCount = iterator_count($sourceFiles);
        echo "    Source files: $sourceCount\n";
    }

    if ($targetExists) {
        $targetFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $targetCount = iterator_count($targetFiles);
        echo "    Target files: $targetCount\n";
    }

    echo "\n";
}

echo "\n📋 RECOMMENDATIONS\n";
echo "==================\n";
echo "1. Copy new Models from app/Models\n";
echo "2. Copy new Controllers from app/Http/Controllers\n";
echo "3. Copy Filament Resources from app/Filament\n";
echo "4. Copy new migrations from database/migrations\n";
echo "5. Copy seeders from database/seeders\n";
echo "6. Copy views from resources/views\n";
echo "7. Merge routes carefully\n";
echo "8. Review config changes\n";
echo "9. Copy public assets if needed\n";

echo "\n⚠️  IMPORTANT FILES TO PRESERVE IN TARGET:\n";
echo "- .env (database configuration)\n";
echo "- composer.json/package.json (dependencies)\n";
echo "- config/database.php (tenancy setup)\n";
echo "- Any tenant-specific configurations\n";
