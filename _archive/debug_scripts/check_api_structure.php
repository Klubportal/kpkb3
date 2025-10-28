<?php

$username = 'nkprigorje';
$password = '3c6nR$dS';

$url = "https://api-hns.analyticom.de/api/export/comet/competitions?teamFifaId=396&active=true";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "=== ERSTE COMPETITION STRUCTURE ===\n\n";
if ($data && count($data) > 0) {
    print_r($data[0]);
}
