<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CometCompetition;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

echo "【 COMET API - Syncing 11 Real Competitions for Club 598 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$apiUrl = 'https://api-hns.analyticom.de';
$apiUsername = 'nkprigorje';
$apiPassword = '3c6nR$dS';

$client = new Client([
    'auth' => [$apiUsername, $apiPassword],
    'timeout' => 30,
    'connect_timeout' => 10,
    'verify' => false,
]);

try {
    echo "📡 Step 1: Fetching competitions from Comet API\n";
    echo "   GET /api/export/comet/competitions?active=true&teamFifaId=598\n\n";

    $response = $client->request('GET', "{$apiUrl}/api/export/comet/competitions", [
        'query' => [
            'active' => 'true',
            'teamFifaId' => 598
        ]
    ]);

    $competitions = json_decode($response->getBody(), true);

    echo "✅ Found " . count($competitions) . " active competitions\n\n";

    // ============================================================
    // Step 2: Clear existing competitions
    // ============================================================
    echo "📝 Step 2: Clearing existing competitions\n";
    DB::table('comet_competitions')->delete();
    echo "✅ Database cleared\n\n";

    // ============================================================
    // Step 3: Insert competitions into database
    // ============================================================
    echo "💾 Step 3: Inserting competitions into database\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    $counter = 0;
    foreach ($competitions as $comp) {
        $counter++;
        $fifaId = $comp['competitionFifaId'] ?? null;

        if (!$fifaId) {
            echo "  ⚠️  Skipping - No FIFA ID\n";
            continue;
        }

        $name = $comp['internationalName'] ?? 'Unknown';
        $shortName = $comp['internationalShortName'] ?? '';
        $season = $comp['season'] ?? 2025;

        // Determine competition type from name
        $competitionType = 'League'; // default
        $lowerName = strtolower($name);

        // Check for "KUP" as a word (not part of "SKUPINA")
        if (preg_match('/\bkup\b/i', $name)) {
            $competitionType = 'Cup';
        } elseif (stripos($lowerName, 'championship') !== false || stripos($lowerName, 'prvenstvo') !== false) {
            $competitionType = 'Championship';
        } elseif (stripos($lowerName, 'liga') !== false || stripos($lowerName, 'league') !== false) {
            $competitionType = 'League';
        }

        // Map age category label to proper enum value
        $ageCategoryLabel = $comp['ageCategoryName'] ?? 'label.category.seniors';
        $ageCategory = 'SENIORS'; // default        // Map label keys to enum values
        if (stripos($ageCategoryLabel, 'label.seniors') !== false || stripos($ageCategoryLabel, 'SENIOR') !== false) {
            $ageCategory = 'SENIORS';
        } elseif (stripos($ageCategoryLabel, 'label.juniors') !== false || stripos($ageCategoryLabel, 'JUNIOR') !== false) {
            $ageCategory = 'U_21'; // Juniors typically U21
        } elseif (stripos($ageCategoryLabel, 'label.cadets') !== false || stripos($ageCategoryLabel, 'CADET') !== false) {
            $ageCategory = 'U_18'; // Cadets typically U18
        } elseif (stripos($ageCategoryLabel, 'label.pioneers') !== false || stripos($ageCategoryLabel, 'PIONEER') !== false) {
            $ageCategory = 'U_15'; // Pioneers typically U15
        } elseif (stripos($ageCategoryLabel, 'label.youngPioneers') !== false || stripos($ageCategoryLabel, 'YOUNG_PIONEER') !== false) {
            $ageCategory = 'U_13'; // Young pioneers typically U13
        } elseif (stripos($ageCategoryLabel, 'label.category.beginners') !== false || stripos($ageCategoryLabel, 'BEGINNER') !== false) {
            $ageCategory = 'U_11'; // Beginners typically U11
        } elseif (stripos($ageCategoryLabel, 'label.kids') !== false || stripos($ageCategoryLabel, 'KID') !== false) {
            $ageCategory = 'U_10'; // Kids typically U10
        } elseif (stripos($ageCategoryLabel, 'label.veterans') !== false || stripos($ageCategoryLabel, 'VETERAN') !== false) {
            $ageCategory = 'A'; // Veterans get special 'A' category
        } else {
            $ageCategory = 'OTHER'; // Fallback
        }

        $gender = $comp['gender'] ?? 'MALE';
        $status = $comp['status'] ?? 'ACTIVE';

        // Map nature values: ELIMINATION -> KNOCKOUT
        $nature = $comp['nature'] ?? 'OTHER';
        if ($nature === 'ELIMINATION') {
            $nature = 'KNOCKOUT';
        }

        $dateFrom = $comp['dateFrom'] ?? null;
        $dateTo = $comp['dateTo'] ?? null;
        $teamChar = $comp['teamCharacter'] ?? 'CLUB';
        $matchType = $comp['matchType'] ?? 'OFFICIAL';
        $discipline = $comp['discipline'] ?? 'FOOTBALL';
        $orgFifaId = $comp['organisationFifaId'] ?? 598;
        $multiplier = $comp['multiplier'] ?? 1;
        $numParticipants = $comp['numberOfParticipants'] ?? 0;
        $penaltyShootout = $comp['penaltyShootout'] ?? false;
        $flyingSubstitutions = $comp['flyingSubstitutions'] ?? false;
        $imageId = $comp['imageId'] ?? null;
        $picture = isset($comp['picture']) ? json_encode($comp['picture']) : null;
        $localNames = isset($comp['localNames']) ? json_encode($comp['localNames']) : null;
        $ageCategoryNameLabel = $comp['ageCategoryName'] ?? 'label.category.seniors';

        try {
            CometCompetition::create([
                'competition_fifa_id' => $fifaId,
                'international_name' => $name,
                'international_short_name' => $shortName,
                'competition_type' => $competitionType,
                'season' => $season,
                'age_category' => $ageCategory,
                'age_category_name' => $ageCategoryNameLabel,
                'gender' => $gender,
                'status' => $status,
                'nature' => $nature,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'team_character' => $teamChar,
                'match_type' => $matchType,
                'discipline' => $discipline,
                'organisation_fifa_id' => $orgFifaId,
                'multiplier' => $multiplier,
                'number_of_participants' => $numParticipants,
                'penalty_shootout' => $penaltyShootout,
                'flying_substitutions' => $flyingSubstitutions,
                'image_id' => $imageId,
                'picture' => $picture,
                'local_names' => $localNames,
                'synced_at' => now(),
            ]);

            echo "  ✅ {$counter}. {$name}\n";

        } catch (\Exception $e) {
            echo "  ❌ {$counter}. Error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n════════════════════════════════════════════════════════════════\n\n";

    // ============================================================
    // Step 4: Verify
    // ============================================================
    echo "🔍 Step 4: Verification\n";
    $total = CometCompetition::count();
    $active = CometCompetition::where('status', 'ACTIVE')->count();

    echo "   Total in DB: {$total}\n";
    echo "   Active: {$active}\n\n";

    // ============================================================
    // Step 5: Display all synced competitions
    // ============================================================
    echo "📋 Step 5: All Synced Competitions\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    $synced = CometCompetition::orderBy('competition_fifa_id')->get();
    foreach ($synced as $idx => $comp) {
        echo ($idx + 1) . ". " . $comp->international_name . "\n";
        echo "   FIFA ID: " . $comp->competition_fifa_id . "\n";
        echo "   Season: " . $comp->season . " | Status: " . $comp->status . "\n";
        echo "   Age: " . $comp->age_category . " | Gender: " . $comp->gender . "\n\n";
    }

    echo "════════════════════════════════════════════════════════════════\n";
    echo "✅ DONE! All 11 real competitions from Comet API synced to database\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
