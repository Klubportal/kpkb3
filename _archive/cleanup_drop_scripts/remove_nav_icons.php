<?php

$files = glob(__DIR__ . '/app/Filament/Pages/Portal/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Remove navigationIcon lines completely
    $updated = preg_replace('/\s*protected static.*\$navigationIcon.*\n/', '', $content);

    if ($updated !== $content) {
        file_put_contents($file, $updated);
        echo "✅ Removed navigationIcon: " . basename($file) . "\n";
    }
}

echo "\n✅ Done!\n";
?>
