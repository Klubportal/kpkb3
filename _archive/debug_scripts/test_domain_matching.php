<?php

echo "=== DOMAIN MATCHING TEST ===\n\n";

// Test domain patterns
$testHosts = [
    'localhost',
    'testclub.localhost',
    'admin.klubportal.com',
    'example.com',
];

$domainPattern = '{tenant}.localhost';

echo "Pattern: {$domainPattern}\n\n";

foreach ($testHosts as $host) {
    // Convert {tenant}.localhost to regex
    $pattern = str_replace('.', '\.', $domainPattern);
    $pattern = str_replace('{tenant}', '([^.]+)', $pattern);
    $pattern = '/^' . $pattern . '$/';

    $matches = preg_match($pattern, $host);

    echo str_pad($host, 30) . " → " . ($matches ? "✓ MATCHES (Tenant Route!)" : "✗ No Match") . "\n";

    if ($matches) {
        preg_match($pattern, $host, $tenantMatches);
        if (isset($tenantMatches[1])) {
            echo str_repeat(' ', 30) . "   Tenant ID: {$tenantMatches[1]}\n";
        }
    }
}

echo "\n=== PROBLEM ===\n";
echo "'localhost' sollte NICHT matchen!\n";
echo "Nur 'xxx.localhost' sollte matchen.\n\n";

echo "Aber: Wird Tenancy durch andere Middleware initialisiert?\n";
echo "Check: bootstrap/app.php Web Middleware\n";
echo "Check: Filament Panel Middleware\n";
