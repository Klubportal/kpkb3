<?php

// DIRECT OUTPUT - Kein Laravel Bootstrap
header('Content-Type: text/plain');

echo "=== REQUEST DEBUG ===\n\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "HTTP Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
echo "Remote Addr: " . $_SERVER['REMOTE_ADDR'] . "\n\n";

echo "=== COOKIES ===\n";
if (!empty($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        echo "{$name}: " . substr($value, 0, 50) . "...\n";
    }
} else {
    echo "Keine Cookies vorhanden\n";
}

echo "\n=== SESSION ===\n";
echo "Session ID: " . (session_id() ?: 'Keine Session gestartet') . "\n";

echo "\n=== HEADERS ===\n";
foreach (getallheaders() as $name => $value) {
    echo "{$name}: {$value}\n";
}

echo "\n=== ANALYSE ===\n";
if ($_SERVER['HTTP_HOST'] === 'localhost:8000') {
    echo "✓ Du bist auf localhost:8000 (Central)\n";
} elseif (str_contains($_SERVER['HTTP_HOST'], '.localhost')) {
    echo "✗ Du bist auf einer SUBDOMAIN: {$_SERVER['HTTP_HOST']} (Tenant!)\n";
    echo "   Das ist NICHT die Central-Domain!\n";
} else {
    echo "? Unbekannte Domain: {$_SERVER['HTTP_HOST']}\n";
}
