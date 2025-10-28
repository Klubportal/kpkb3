<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Test: Hole Top Scorers von der ersten Competition
$competitionId = 100629221; // PRVA ZAGREBAČKA LIGA

echo "Testing API response for competition $competitionId:\n";
echo str_repeat("=", 70) . "\n\n";

$response = Http::withBasicAuth($username, $password)
    ->get("{$baseUrl}/competition/{$competitionId}/topScorers");

if ($response->successful()) {
    $scorers = $response->json();

    echo "Total scorers: " . count($scorers) . "\n\n";
    echo "First scorer structure:\n";
    print_r($scorers[0] ?? 'No data');
} else {
    echo "Error: " . $response->status() . "\n";
    echo $response->body();
}
