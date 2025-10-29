<?php

$file = 'resources/views/templates/fcbm/home.blade.php';
$lines = file($file, FILE_IGNORE_NEW_LINES);

echo "=== Detaillierte Blade Analyse ===\n\n";

$foreachStack = [];
$forelsStack = [];

foreach ($lines as $index => $line) {
    $lineNum = $index + 1;

    if (preg_match('/@foreach\s*\(/', $line)) {
        $foreachStack[] = $lineNum;
        echo "Line $lineNum: @foreach ÖFFNET\n";
        echo "  Stack: " . json_encode($foreachStack) . "\n";
    }

    if (preg_match('/@endforeach/', $line)) {
        if (empty($foreachStack)) {
            echo "❌ Line $lineNum: @endforeach OHNE @foreach!\n";
        } else {
            $openLine = array_pop($foreachStack);
            echo "Line $lineNum: @endforeach schließt @foreach von Zeile $openLine\n";
            echo "  Stack: " . json_encode($foreachStack) . "\n";
        }
    }

    if (preg_match('/@forelse\s*\(/', $line)) {
        $forelsStack[] = $lineNum;
        echo "Line $lineNum: @forelse ÖFFNET\n";
        echo "  Stack: " . json_encode($forelsStack) . "\n";
    }

    if (preg_match('/@endforelse/', $line)) {
        if (empty($forelsStack)) {
            echo "❌ Line $lineNum: @endforelse OHNE @forelse!\n";
        } else {
            $openLine = array_pop($forelsStack);
            echo "Line $lineNum: @endforelse schließt @forelse von Zeile $openLine\n";
            echo "  Stack: " . json_encode($forelsStack) . "\n";
        }
    }
}

echo "\n=== Finale Stacks ===\n";
echo "@foreach unclosed: " . json_encode($foreachStack) . "\n";
echo "@forelse unclosed: " . json_encode($forelsStack) . "\n";
