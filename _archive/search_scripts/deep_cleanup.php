<?php

$files = glob(__DIR__ . '/app/Filament/Pages/Portal/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Split into lines
    $lines = explode("\n", $content);
    $cleaned = [];
    $seen_props = [];

    foreach ($lines as $line) {
        // Check if line contains a navigation property declaration
        if (preg_match('/protected static .*\$(navigationIcon|navigationLabel|navigationSort)/', $line)) {
            // Create a normalized key for comparison (ignore type hints and slight variations)
            if (preg_match('/\$(\w+)/', $line, $match)) {
                $propName = $match[1];

                // If we've seen this property already, skip it
                if (in_array($propName, $seen_props)) {
                    continue;
                }
                $seen_props[] = $propName;
            }
        }

        $cleaned[] = $line;
    }

    $updated = implode("\n", $cleaned);

    if ($updated !== $content) {
        file_put_contents($file, $updated);
        echo "✅ Cleaned: " . basename($file) . "\n";
    }
}

echo "\n✅ All files cleaned!\n";
?>
