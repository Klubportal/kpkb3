<?php
/**
 * Emergency APP_KEY Generator
 * Upload this file to /public_html/kpkb3/ and access it via browser
 * It will automatically generate and update the APP_KEY in .env
 */

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    die('ERROR: .env file not found at: ' . $envFile);
}

// Generate a secure random key
$key = 'base64:' . base64_encode(random_bytes(32));

// Read .env file
$envContent = file_get_contents($envFile);

// Check if APP_KEY exists
if (strpos($envContent, 'APP_KEY=') !== false) {
    // Replace existing APP_KEY
    $envContent = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . $key, $envContent);
} else {
    // Add APP_KEY if it doesn't exist
    $envContent .= "\nAPP_KEY=" . $key . "\n";
}

// Write back to .env
if (file_put_contents($envFile, $envContent)) {
    echo "<h1>✅ SUCCESS!</h1>";
    echo "<p>APP_KEY has been generated and saved to .env</p>";
    echo "<p><strong>Generated Key:</strong> <code>" . htmlspecialchars($key) . "</code></p>";
    echo "<hr>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Delete this file (generate_key.php) from the server for security</li>";
    echo "<li>Visit <a href='https://kpaktiv.de'>https://kpaktiv.de</a> - your site should now work!</li>";
    echo "</ol>";
    echo "<hr>";
    echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file immediately after use!</p>";
} else {
    echo "<h1>❌ ERROR</h1>";
    echo "<p>Could not write to .env file. Check file permissions.</p>";
    echo "<p>You can manually add this key to your .env file:</p>";
    echo "<p><code>APP_KEY=" . htmlspecialchars($key) . "</code></p>";
}
?>
