<?php

$files = glob(__DIR__ . '/app/Filament/Pages/Portal/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Check if shouldRegisterNavigation already exists
    if (strpos($content, 'shouldRegisterNavigation') !== false) {
        echo "⏭️  Skip (already has shouldRegisterNavigation): " . basename($file) . "\n";
        continue;
    }

    // Find the class opening brace and add method after navigationSort
    // Pattern: find "protected static ?int $navigationSort"
    $pattern = '/(protected static \?int \$navigationSort = \d+;)/';

    if (!preg_match($pattern, $content)) {
        echo "⚠️  Could not find navigationSort: " . basename($file) . "\n";
        continue;
    }

    $replacement = "$1\n\n    public static function shouldRegisterNavigation(): bool\n    {\n        return true;\n    }";

    $updated = preg_replace($pattern, $replacement, $content, 1);

    if ($updated !== $content) {
        file_put_contents($file, $updated);
        echo "✅ Added shouldRegisterNavigation: " . basename($file) . "\n";
    }
}

echo "\n✅ Done!\n";
?>
