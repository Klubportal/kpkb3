<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\CometApiService;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$api = new CometApiService();

// Test mit einem Match das Events hat
$matchId = 100860270;

echo "🔍 TEST MATCH EVENTS API für Match {$matchId}\n";
echo str_repeat("=", 60) . "\n\n";

try {
    $events = $api->getMatchEvents($matchId);

    echo "📊 Anzahl Events: " . count($events) . "\n\n";

    if (!empty($events)) {
        echo "📄 Erstes Event (Struktur):\n";
        print_r($events[0]);

        echo "\n\n📄 Alle Events (kurz):\n";
        foreach ($events as $i => $event) {
            echo ($i + 1) . ". ";
            echo "Minute: " . ($event['eventMinute'] ?? $event['minute'] ?? '?');
            echo " | Type: " . ($event['eventType'] ?? '?');
            echo " | Player: " . ($event['playerName'] ?? '?');
            echo " | ID: " . ($event['matchEventFifaId'] ?? 'NONE');
            echo "\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
}
