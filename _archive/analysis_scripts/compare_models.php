<?php

echo "🔍 COMPARING MODELS BETWEEN PROJECTS\n";
echo "===================================\n\n";

$source = 'c:\xampp\htdocs\kp_club_management\app\Models';
$target = 'c:\xampp\htdocs\kpkb3\app\Models';

function getPhpFiles($directory) {
    $files = [];
    if (is_dir($directory)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $relativePath = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $files[] = str_replace('\\', '/', $relativePath);
            }
        }
    }
    return $files;
}

$sourceModels = getPhpFiles($source);
$targetModels = getPhpFiles($target);

echo "📂 SOURCE MODELS (kp_club_management):\n";
echo "=====================================\n";
foreach ($sourceModels as $model) {
    echo "- $model\n";
}

echo "\n📂 TARGET MODELS (kpkb3):\n";
echo "========================\n";
foreach ($targetModels as $model) {
    echo "- $model\n";
}

echo "\n🔍 ANALYSIS:\n";
echo "===========\n";

$onlyInSource = array_diff($sourceModels, $targetModels);
$onlyInTarget = array_diff($targetModels, $sourceModels);
$inBoth = array_intersect($sourceModels, $targetModels);

echo "✅ Models in BOTH projects (" . count($inBoth) . "):\n";
foreach ($inBoth as $model) {
    echo "   - $model\n";
}

echo "\n📥 Models ONLY in SOURCE (kp_club_management) - " . count($onlyInSource) . " files:\n";
foreach ($onlyInSource as $model) {
    echo "   - $model ⬅️ SHOULD COPY\n";
}

echo "\n📤 Models ONLY in TARGET (kpkb3) - " . count($onlyInTarget) . " files:\n";
foreach ($onlyInTarget as $model) {
    echo "   - $model ✅ NEW DEVELOPMENT\n";
}

echo "\n🎯 RECOMMENDATION:\n";
echo "=================\n";
if (count($onlyInSource) > 0) {
    echo "Copy these " . count($onlyInSource) . " models from kp_club_management to kpkb3:\n";
    foreach ($onlyInSource as $model) {
        echo "   xcopy \"$source\\$model\" \"$target\\$model\" /Y\n";
    }
} else {
    echo "✅ No models need to be copied. Target has all source models plus additional ones.\n";
}

echo "\n💡 NOTE: The kpkb3 project appears to be more advanced with additional models.\n";
