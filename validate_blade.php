<?php

$file = 'resources/views/templates/fcbm/home.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

echo "=== Blade Directive Validator ===\n\n";

$stack = [];
$lineNum = 0;

foreach ($lines as $line) {
    $lineNum++;

    // Find all blade directives
    if (preg_match('/@if\s*\(/', $line)) {
        $stack[] = ['type' => 'if', 'line' => $lineNum, 'content' => trim($line)];
    }
    elseif (preg_match('/@elseif\s*\(/', $line)) {
        // elseif doesn't change stack, just note it
    }
    elseif (preg_match('/@else\s*$/', $line) || preg_match('/@else\s+/', $line)) {
        // else doesn't change stack
    }
    elseif (preg_match('/@endif/', $line)) {
        if (empty($stack) || end($stack)['type'] !== 'if') {
            echo "❌ Line $lineNum: @endif without matching @if\n";
            echo "   Stack: " . json_encode($stack) . "\n\n";
        } else {
            array_pop($stack);
        }
    }
    elseif (preg_match('/@foreach\s*\(/', $line)) {
        $stack[] = ['type' => 'foreach', 'line' => $lineNum, 'content' => trim($line)];
    }
    elseif (preg_match('/@endforeach/', $line)) {
        if (empty($stack) || end($stack)['type'] !== 'foreach') {
            echo "❌ Line $lineNum: @endforeach without matching @foreach\n";
            echo "   Current line: " . trim($line) . "\n";
            echo "   Stack top: " . (empty($stack) ? 'empty' : json_encode(end($stack))) . "\n\n";
        } else {
            array_pop($stack);
        }
    }
    elseif (preg_match('/@forelse\s*\(/', $line)) {
        $stack[] = ['type' => 'forelse', 'line' => $lineNum, 'content' => trim($line)];
    }
    elseif (preg_match('/@empty/', $line)) {
        // @empty is part of @forelse, doesn't change stack
    }
    elseif (preg_match('/@endforelse/', $line)) {
        if (empty($stack) || end($stack)['type'] !== 'forelse') {
            echo "❌ Line $lineNum: @endforelse without matching @forelse\n";
            echo "   Stack: " . json_encode($stack) . "\n\n";
        } else {
            array_pop($stack);
        }
    }
}

echo "\n=== Final Stack (should be empty) ===\n";
if (empty($stack)) {
    echo "✅ All directives properly closed!\n";
} else {
    echo "❌ Unclosed directives:\n";
    foreach ($stack as $item) {
        echo "   Line {$item['line']}: {$item['type']} - {$item['content']}\n";
    }
}
