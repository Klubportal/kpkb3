<?php

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "Log file not found!\n";
    exit(1);
}

// Read last 5000 lines
$lines = file($logFile);
$lastLines = array_slice($lines, -5000);

$errors = [];
$currentError = [];
$inError = false;

foreach ($lastLines as $line) {
    // Check if this is a new error entry
    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.ERROR:/', $line, $matches)) {
        // Save previous error if exists
        if (!empty($currentError)) {
            $errors[] = $currentError;
        }

        // Start new error
        $currentError = [
            'time' => $matches[1],
            'channel' => $matches[2],
            'lines' => [$line]
        ];
        $inError = true;
    } elseif ($inError) {
        // Continue collecting error lines
        $currentError['lines'][] = $line;

        // Stop at stack trace end or next log entry
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2})/', $line) && !preg_match('/^#\d+/', $line)) {
            $inError = false;
        }
    }
}

// Add last error
if (!empty($currentError)) {
    $errors[] = $currentError;
}

// Show last 3 errors
$lastErrors = array_slice($errors, -3);

foreach ($lastErrors as $error) {
    echo "\n========================================\n";
    echo "Time: " . $error['time'] . "\n";
    echo "Channel: " . $error['channel'] . "\n";
    echo "----------------------------------------\n";
    echo implode('', array_slice($error['lines'], 0, 30)); // First 30 lines
    echo "\n";
}
