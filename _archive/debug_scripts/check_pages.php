<?php
// Check all Portal Pages in SuperAdmin/Pages directory
$pagesDir = 'app/Filament/SuperAdmin/Pages';
$files = array_diff(scandir($pagesDir), ['.', '..', 'ClubManagement.php', 'ClubDetails.php']);

echo "📋 Checking Portal Pages Configuration:\n\n";

foreach ($files as $file) {
    if (substr($file, -4) !== '.php') continue;

    $filePath = "$pagesDir/$file";
    $content = file_get_contents($filePath);

    $hasNavigationLabel = strpos($content, '$navigationLabel') !== false;
    $hasNavigationSort = strpos($content, '$navigationSort') !== false;
    $hasShouldRegister = strpos($content, 'shouldRegisterNavigation') !== false;

    $status = ($hasNavigationLabel && $hasNavigationSort && $hasShouldRegister) ? '✅' : '❌';

    echo "$status $file\n";
    if (!$hasNavigationLabel) echo "   Missing: navigationLabel\n";
    if (!$hasNavigationSort) echo "   Missing: navigationSort\n";
    if (!$hasShouldRegister) echo "   Missing: shouldRegisterNavigation()\n";
}

echo "\n✅ Check complete!\n";
