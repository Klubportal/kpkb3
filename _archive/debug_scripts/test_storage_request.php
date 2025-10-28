<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Simulate request
$request = \Illuminate\Http\Request::create(
    'http://nknapijed.localhost:8000/storage/logos/01K8KSS023SW8CYGG1QM6G5GZG.png',
    'GET'
);

$request->headers->set('Host', 'nknapijed.localhost:8000');

try {
    $response = $kernel->handle($request);

    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "Content-Length: " . strlen($response->getContent()) . " bytes\n";

    if ($response->getStatusCode() === 200) {
        echo "\nSUCCESS! Storage route is working!\n";
    } else {
        echo "\nResponse content:\n";
        echo substr($response->getContent(), 0, 500) . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

$kernel->terminate($request, $response ?? null);
