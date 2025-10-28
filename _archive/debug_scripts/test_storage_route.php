<?php

$url = 'http://nknapijed.localhost:8000/storage/logos/01K8KSS023SW8CYGG1QM6G5GZG.png';

echo "Testing URL: $url\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Content-Type: $contentType\n";

if ($httpCode === 200) {
    $body = substr($response, $headerSize);
    echo "Response size: " . number_format(strlen($body) / 1024, 2) . " KB\n";
    echo "SUCCESS: Image can be loaded!\n";
} else {
    echo "ERROR: Cannot load image\n";
    echo "Response headers:\n";
    echo substr($response, 0, $headerSize);
}
