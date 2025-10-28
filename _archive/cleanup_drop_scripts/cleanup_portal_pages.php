<?php

$files = glob(__DIR__ . '/app/Filament/Pages/Portal/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Remove duplicate navigationIcon, navigationLabel, navigationSort lines
    $lines = explode("\n", $content);
    $cleaned = [];
    $seen_nav_props = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Check if this is a nav property we've already seen
        if (preg_match('/protected static.*\$(navigationIcon|navigationLabel|navigationSort)/', $trimmed)) {
            if (in_array($trimmed, $seen_nav_props)) {
                // Skip duplicate
                continue;
            }
            $seen_nav_props[] = $trimmed;
        }

        $cleaned[] = $line;
    }

    $updated = implode("\n", $cleaned);

    if ($updated !== $content) {
        file_put_contents($file, $updated);
        echo "✅ Cleaned duplicates: " . basename($file) . "\n";
    }
}

echo "\n✅ All files cleaned!\n";
?>
