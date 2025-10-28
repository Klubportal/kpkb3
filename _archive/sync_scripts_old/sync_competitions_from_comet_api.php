<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

/**
 * Detect age category from competition name (Croatian patterns)
 */
function detectAgeCategoryFromName($name) {
    $name = mb_strtolower($name);

    // Patterns for different age categories
    if (preg_match('/\b(senior|seniori|veterani|veteran)\b/i', $name)) {
        return 'SENIORS';
    }
    if (preg_match('/\b(junior|juniori|u-?19|u19)\b/i', $name)) {
        return 'JUNIORS';
    }
    if (preg_match('/\b(kadet|kadeti|kadetkinje|u-?17|u17)\b/i', $name)) {
        return 'CADETS';
    }
    if (preg_match('/\b(pionir|pioniri|pionirke|stariji pioniri|u-?15|u15)\b/i', $name)) {
        return 'PIONEERS';
    }
    if (preg_match('/\b(mlađi pioniri|mladi pioniri|limač|limači|u-?13|u13)\b/i', $name)) {
        return 'YOUNG_PIONEERS';
    }
    if (preg_match('/\b(prstić|prstići|početnici|u-?11|u11)\b/i', $name)) {
        return 'BEGINNERS';
    }
    if (preg_match('/\b(žen|women|female|kadetkinje|pionirke)\b/i', $name)) {
        return 'WOMEN';
    }

    return 'OTHER';
}

const CLUB_FIFA_ID = 598;

echo "【 Syncing Real Competitions from Comet REST API 】\n";
echo "════════════════════════════════════════════════════════════════\n";

// Get API credentials
$apiUrl = env('COMET_API_BASE_URL');
$apiUsername = env('COMET_API_USERNAME');
$apiPassword = env('COMET_API_PASSWORD');

if (!$apiUrl || !$apiUsername || !$apiPassword) {
    echo "❌ Comet API credentials not found in .env\n";
    exit(1);
}

echo "✅ Using Comet API: {$apiUrl}\n";
echo "✅ Username: {$apiUsername}\n\n";

echo "【 Step 1: Fetch All Competitions from Comet API 】\n";

$client = new Client([
    'base_uri' => $apiUrl,
    'auth' => [$apiUsername, $apiPassword],
    'timeout' => 30,
    'connect_timeout' => 10,
]);

try {
    echo "📡 Calling: GET /api/export/comet/competitions\n";

    $response = $client->request('GET', '/api/export/comet/competitions', [
        'query' => [
            'active' => 'true',
            'season' => 2025,
        ]
    ]);

    $allComps = json_decode($response->getBody(), true);

    if (!is_array($allComps)) {
        $allComps = [$allComps];
    }

    echo "✅ Retrieved " . count($allComps) . " competitions from API\n\n";

} catch (\Exception $e) {
    echo "❌ API Error: {$e->getMessage()}\n";
    exit(1);
}

// Step 2: Filter competitions for this club
echo "【 Step 2: Filter Competitions for Club 598 】\n";

$clubComps = [];
foreach ($allComps as $comp) {
    // Check if club participates or if it's in the organization
    if (
        (isset($comp['organisationFifaId']) && $comp['organisationFifaId'] == CLUB_FIFA_ID) ||
        (isset($comp['clubId']) && $comp['clubId'] == CLUB_FIFA_ID) ||
        (isset($comp['fifaId']) && strpos(json_encode($comp), (string)CLUB_FIFA_ID) !== false)
    ) {
        $clubComps[] = $comp;
    }
}

echo "✅ Found " . count($clubComps) . " competitions with Club 598\n\n";

// Step 3: Sync to database
echo "【 Step 3: Sync Competitions to Database 】\n";

// First clear old competitions
DB::table('comet_competitions')->delete();
echo "🗑️  Cleared old competitions\n\n";

$synced = 0;
$failed = 0;

foreach ($allComps as $comp) {
    try {
        // Map API fields to database fields
        $compId = $comp['competitionFifaId'] ?? null;

        if (!$compId) {
            continue;
        }

        $compData = [
            'comet_id' => $compId,
            'name' => $comp['internationalName'] ?? "Competition {$compId}",
            'slug' => \Illuminate\Support\Str::slug($comp['internationalName'] ?? "comp-{$compId}") . '-' . $compId,
            'description' => $comp['description'] ?? null,
            'organisation_fifa_id' => $comp['organisationFifaId'] ?? CLUB_FIFA_ID,
            'season' => $comp['season'] ?? '2025',
            'type' => strtolower($comp['competitionType'] ?? 'league'),
            'age_category' => ($comp['ageCategory'] ?? 'OTHER') !== 'OTHER'
                ? $comp['ageCategory']
                : detectAgeCategoryFromName($comp['internationalName'] ?? ''),
            'gender' => $comp['gender'] ?? 'MALE',
            'team_character' => $comp['teamCharacter'] ?? 'CLUB',
            'nature' => $comp['nature'] ?? 'ROUND_ROBIN',
            'match_type' => $comp['matchType'] ?? 'OFFICIAL',
            'participants' => $comp['numberOfParticipants'] ?? 0,
            'start_date' => isset($comp['dateFrom']) ? \Carbon\Carbon::parse($comp['dateFrom'])->format('Y-m-d') : null,
            'end_date' => isset($comp['dateTo']) ? \Carbon\Carbon::parse($comp['dateTo'])->format('Y-m-d') : null,
            'status' => strtolower($comp['status'] ?? 'active'),
            'active' => ($comp['status'] ?? 'ACTIVE') === 'ACTIVE',
            'logo_url' => $comp['imageUrl'] ?? null,
            'image_id' => $comp['imageId'] ?? null,
            'local_names' => isset($comp['localNames']) ? json_encode($comp['localNames']) : null,
            'settings' => json_encode([
                'penalty_shootout' => $comp['penaltyShootout'] ?? false,
                'flying_substitutions' => $comp['flyingSubstitutions'] ?? false,
                'ranking_notes' => $comp['rankingNotes'] ?? null,
                'multiplier' => $comp['multiplier'] ?? 1,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('comet_competitions')->insert($compData);

        echo "✅ {$compData['name']} ({$compData['age_category']})\n";
        $synced++;

    } catch (\Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
        $failed++;
    }
}

// Step 4: Summary
echo "\n【 Step 4: Summary 】\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "✅ Synced: {$synced}\n";
if ($failed > 0) {
    echo "❌ Failed: {$failed}\n";
}

$total = DB::table('comet_competitions')->count();
echo "✅ Total in Database: {$total}\n";

echo "\n✅ REAL COMPETITIONS SYNC COMPLETE!\n";
