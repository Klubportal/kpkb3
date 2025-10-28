<?php

$ch = curl_init('https://api-hns.analyticom.de/api/export/comet/competition/442/topScorers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, 'nkprigorje:3c6nR$dS');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Code: {$httpCode}\n";
$data = json_decode($response, true);
echo "Total scorers: " . count($data) . "\n";
echo "First 2 top scorers:\n";
print_r(array_slice($data, 0, 2));

?>
