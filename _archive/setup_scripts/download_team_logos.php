<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   DOWNLOAD TEAM LOGOS FROM COMET API                         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// API Config
$baseUrl = 'https://api-hns.analyticom.de/api/export/comet';
$username = 'nkprigorje';
$password = '3c6nR$dS';

// Logo directory
$logoDir = public_path('images/kp_team_logo_images');
if (!File::exists($logoDir)) {
    File::makeDirectory($logoDir, 0755, true);
    echo "✓ Created logo directory: $logoDir\n\n";
}

// Hole alle einzigartigen club_ids aus top_scorers
$clubIds = DB::connection('central')
    ->table('comet_top_scorers')
    ->select('club_id')
    ->where('club_id', '>', 0)
    ->distinct()
    ->pluck('club_id');

echo "Found " . $clubIds->count() . " unique clubs\n";
echo str_repeat("=", 70) . "\n\n";

$downloaded = 0;
$failed = 0;
$skipped = 0;

foreach ($clubIds as $clubId) {
    // Prüfe ob Logo bereits existiert
    $existingLogo = null;
    foreach (['png', 'jpg', 'jpeg', 'gif'] as $ext) {
        if (File::exists("$logoDir/$clubId.$ext")) {
            $existingLogo = "$clubId.$ext";
            break;
        }
    }

    if ($existingLogo) {
        $skipped++;
        continue;
    }

    try {
        // Hole Club-Daten von API
        $response = Http::withBasicAuth($username, $password)
            ->timeout(10)
            ->get("{$baseUrl}/club/{$clubId}");

        if ($response->successful()) {
            $clubData = $response->json();

            if (isset($clubData['logo']) && $clubData['logo']) {
                $logoUrl = $clubData['logo'];

                // Lade Logo herunter
                $logoResponse = Http::withBasicAuth($username, $password)
                    ->timeout(15)
                    ->get($logoUrl);

                if ($logoResponse->successful()) {
                    $content = $logoResponse->body();

                    // Erkenne Bildtyp
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($content);

                    $extMap = [
                        'image/png' => 'png',
                        'image/jpeg' => 'jpg',
                        'image/jpg' => 'jpg',
                        'image/gif' => 'gif'
                    ];

                    $ext = $extMap[$mimeType] ?? 'png';
                    $filename = "$clubId.$ext";
                    $filepath = "$logoDir/$filename";

                    File::put($filepath, $content);

                    // Update Datenbank
                    $logoPath = "images/kp_team_logo_images/$filename";
                    DB::connection('central')
                        ->table('comet_top_scorers')
                        ->where('club_id', $clubId)
                        ->update(['team_logo' => $logoPath]);

                    $clubName = $clubData['internationalName'] ?? $clubData['name'] ?? "Club $clubId";
                    echo "✓ $filename - $clubName\n";
                    $downloaded++;
                } else {
                    echo "⚠ Club $clubId: Logo download failed (HTTP {$logoResponse->status()})\n";
                    $failed++;
                }
            } else {
                echo "⚠ Club $clubId: No logo URL in API response\n";
                $failed++;
            }
        } else {
            echo "⚠ Club $clubId: API request failed (HTTP {$response->status()})\n";
            $failed++;
        }

        // Rate limiting
        usleep(100000); // 100ms delay

    } catch (\Exception $e) {
        echo "❌ Club $clubId: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                     DOWNLOAD SUMMARY                          ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Clubs:          " . str_pad($clubIds->count(), 38) . "║\n";
echo "║ Downloaded:           " . str_pad($downloaded, 38) . "║\n";
echo "║ Skipped (exists):     " . str_pad($skipped, 38) . "║\n";
echo "║ Failed:               " . str_pad($failed, 38) . "║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Finale Statistik
$withLogo = DB::connection('central')->table('comet_top_scorers')->whereNotNull('team_logo')->count();
$total = DB::connection('central')->table('comet_top_scorers')->count();

echo "Top Scorers with team_logo: $withLogo / $total\n\n";

echo "✅ TEAM LOGOS DOWNLOAD COMPLETED!\n";
