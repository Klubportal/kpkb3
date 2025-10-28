<?php

$url = 'http://nknapijed.localhost:8000/storage/logos/01K8KSS023SW8CYGG1QM6G5GZG.png';

echo "Testing: $url\n\n";

// Test mit file_get_contents direkt
try {
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'header' => "Accept: image/*\r\n"
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response !== false) {
        echo "SUCCESS! Got " . number_format(strlen($response) / 1024, 2) . " KB\n";

        // Check HTTP headers
        if (isset($http_response_header)) {
            echo "\nResponse Headers:\n";
            foreach ($http_response_header as $header) {
                echo "$header\n";
            }
        }
    } else {
        echo "FAILED\n";

        if (isset($http_response_header)) {
            echo "\nResponse Headers:\n";
            foreach ($http_response_header as $header) {
                echo "$header\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
