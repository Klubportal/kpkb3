<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   UPDATE TOP SCORERS - COMPLETE DATA                         ║\n";
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

// Get all top scorers
$scorers = DB::connection('central')->table('comet_top_scorers')->get();

echo "Found {$scorers->count()} top scorers to update\n";
echo str_repeat("=", 70) . "\n\n";

$updated = 0;
$clubCache = [];
$logoCache = [];

foreach ($scorers as $scorer) {
    $updates = [];

    // 1. Get player_fifa_id from comet_players table
    $player = DB::connection('central')
        ->table('comet_players')
        ->where('first_name', $scorer->international_first_name)
        ->where('last_name', $scorer->international_last_name)
        ->first();

    if ($player) {
        $updates['player_fifa_id'] = $player->person_fifa_id;
    }

    // 2. Get club name and logo from API (cached by club_id)
    if ($scorer->club_id && $scorer->club_id > 0) {

        if (!isset($clubCache[$scorer->club_id])) {
            try {
                $response = Http::withBasicAuth($username, $password)
                    ->get("{$baseUrl}/club/{$scorer->club_id}");

                if ($response->successful()) {
                    $clubData = $response->json();
                    $clubCache[$scorer->club_id] = [
                        'name' => $clubData['internationalName'] ?? $clubData['name'] ?? null,
                        'logo' => $clubData['logo'] ?? null
                    ];
                } else {
                    $clubCache[$scorer->club_id] = null;
                }
            } catch (\Exception $e) {
                $clubCache[$scorer->club_id] = null;
            }
        }

        if ($clubCache[$scorer->club_id]) {
            $updates['club'] = $clubCache[$scorer->club_id]['name'];

            // 3. Download and save team logo
            if ($clubCache[$scorer->club_id]['logo']) {
                $logoUrl = $clubCache[$scorer->club_id]['logo'];

                if (!isset($logoCache[$scorer->club_id])) {
                    try {
                        // Try different extensions
                        $extensions = ['png', 'jpg', 'jpeg', 'gif'];
                        $savedPath = null;

                        foreach ($extensions as $ext) {
                            $filename = "{$scorer->club_id}.{$ext}";
                            $filepath = "$logoDir/$filename";

                            if (File::exists($filepath)) {
                                $savedPath = "images/kp_team_logo_images/$filename";
                                break;
                            }
                        }

                        if (!$savedPath) {
                            // Try to download logo
                            $logoResponse = Http::withBasicAuth($username, $password)
                                ->timeout(10)
                                ->get($logoUrl);

                            if ($logoResponse->successful()) {
                                $content = $logoResponse->body();

                                // Detect image type
                                $finfo = new finfo(FILEINFO_MIME_TYPE);
                                $mimeType = $finfo->buffer($content);

                                $extMap = [
                                    'image/png' => 'png',
                                    'image/jpeg' => 'jpg',
                                    'image/jpg' => 'jpg',
                                    'image/gif' => 'gif'
                                ];

                                $ext = $extMap[$mimeType] ?? 'png';
                                $filename = "{$scorer->club_id}.{$ext}";
                                $filepath = "$logoDir/$filename";

                                File::put($filepath, $content);
                                $savedPath = "images/kp_team_logo_images/$filename";
                                echo "  📥 Downloaded logo for club {$scorer->club_id}: $filename\n";
                            }
                        }

                        $logoCache[$scorer->club_id] = $savedPath;
                    } catch (\Exception $e) {
                        $logoCache[$scorer->club_id] = null;
                    }
                }

                if ($logoCache[$scorer->club_id]) {
                    $updates['team_logo'] = $logoCache[$scorer->club_id];
                }
            }
        }
    }

    // Update record
    if (!empty($updates)) {
        DB::connection('central')
            ->table('comet_top_scorers')
            ->where('id', $scorer->id)
            ->update($updates);
        $updated++;

        if ($updated % 50 == 0) {
            echo "  ⏳ Updated $updated / {$scorers->count()} scorers...\n";
        }
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                     UPDATE SUMMARY                            ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Scorers:        " . str_pad($scorers->count(), 38) . "║\n";
echo "║ Updated Records:      " . str_pad($updated, 38) . "║\n";
echo "║ Downloaded Logos:     " . str_pad(count(array_filter($logoCache)), 38) . "║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Final statistics
$withPlayerFifaId = DB::connection('central')->table('comet_top_scorers')->whereNotNull('player_fifa_id')->count();
$withClub = DB::connection('central')->table('comet_top_scorers')->whereNotNull('club')->count();
$withTeamLogo = DB::connection('central')->table('comet_top_scorers')->whereNotNull('team_logo')->count();

echo "Updated Fields:\n";
echo "  - player_fifa_id: $withPlayerFifaId / {$scorers->count()}\n";
echo "  - club:           $withClub / {$scorers->count()}\n";
echo "  - team_logo:      $withTeamLogo / {$scorers->count()}\n\n";

echo "✅ TOP SCORERS UPDATE COMPLETED!\n";
