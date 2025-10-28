<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kpkb3';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔄 Updating sync schedules - alle 5 Minuten für Match-Daten...\n";
echo "============================================================\n\n";

// Lösche alte Zeitpläne
$conn->query("DELETE FROM sync_schedules");
echo "🗑️  Alte Zeitpläne gelöscht\n\n";

// KORRIGIERTE Zeitpläne
$schedules = [
    // ALLE 5 MINUTEN (9:00-23:00) - Match-bezogene Daten (-7 bis +14 Tage)
    [
        'sync_type' => 'matches',
        'frequency' => 'custom',
        'schedule_cron' => '*/5 9-23 * * *', // Alle 5 Min zwischen 9-23 Uhr
        'sync_params' => json_encode([
            'date_from' => '-7 days',
            'date_to' => '+14 days',
            'update_mode' => 'upsert' // INSERT oder UPDATE wenn existiert
        ])
    ],
    [
        'sync_type' => 'match_events',
        'frequency' => 'custom',
        'schedule_cron' => '*/5 9-23 * * *',
        'sync_params' => json_encode([
            'date_from' => '-7 days',
            'date_to' => '+14 days',
            'update_mode' => 'upsert'
        ])
    ],
    [
        'sync_type' => 'match_phases',
        'frequency' => 'custom',
        'schedule_cron' => '*/5 9-23 * * *',
        'sync_params' => json_encode([
            'date_from' => '-7 days',
            'date_to' => '+14 days',
            'update_mode' => 'upsert'
        ])
    ],
    [
        'sync_type' => 'match_players',
        'frequency' => 'custom',
        'schedule_cron' => '*/5 9-23 * * *',
        'sync_params' => json_encode([
            'date_from' => '-7 days',
            'date_to' => '+14 days',
            'update_mode' => 'upsert'
        ])
    ],
    [
        'sync_type' => 'rankings',
        'frequency' => 'custom',
        'schedule_cron' => '*/5 9-23 * * *',
        'sync_params' => json_encode([
            'update_mode' => 'upsert'
        ])
    ],
    [
        'sync_type' => 'top_scorers',
        'frequency' => 'custom',
        'schedule_cron' => '*/5 9-23 * * *',
        'sync_params' => json_encode([
            'update_mode' => 'upsert'
        ])
    ],
    [
        'sync_type' => 'players',
        'frequency' => 'custom',
        'schedule_cron' => '*/5 9-23 * * *',
        'sync_params' => json_encode([
            'update_mode' => 'upsert'
        ])
    ],

    // TÄGLICH - Officials
    [
        'sync_type' => 'match_officials',
        'frequency' => 'daily',
        'schedule_time' => '03:00',
        'sync_params' => json_encode([
            'date_from' => '-7 days',
            'date_to' => '+14 days',
            'update_mode' => 'upsert'
        ])
    ],
    [
        'sync_type' => 'match_team_officials',
        'frequency' => 'daily',
        'schedule_time' => '03:30',
        'sync_params' => json_encode([
            'date_from' => '-7 days',
            'date_to' => '+14 days',
            'update_mode' => 'upsert'
        ])
    ],

    // WÖCHENTLICH - Competitions (Sonntag 2:00)
    [
        'sync_type' => 'competitions',
        'frequency' => 'weekly',
        'schedule_time' => '02:00',
        'schedule_day' => 0,
        'sync_params' => json_encode(['update_mode' => 'upsert'])
    ],

    // MONATLICH - Team Officials & Representatives (Sonntag 2:00)
    [
        'sync_type' => 'team_officials',
        'frequency' => 'monthly',
        'schedule_time' => '02:00',
        'schedule_day' => 0,
        'sync_params' => json_encode(['update_mode' => 'upsert'])
    ],
    [
        'sync_type' => 'representatives',
        'frequency' => 'monthly',
        'schedule_time' => '02:00',
        'schedule_day' => 0,
        'sync_params' => json_encode(['update_mode' => 'upsert'])
    ]
];

$inserted = 0;
foreach ($schedules as $schedule) {
    $params = $schedule['sync_params'] ? "'" . $schedule['sync_params'] . "'" : 'NULL';
    $time = isset($schedule['schedule_time']) ? "'" . $schedule['schedule_time'] . "'" : 'NULL';
    $day = isset($schedule['schedule_day']) ? $schedule['schedule_day'] : 'NULL';
    $cron = isset($schedule['schedule_cron']) ? "'" . $schedule['schedule_cron'] . "'" : 'NULL';

    $sql = "INSERT INTO sync_schedules
            (tenant_id, sync_type, frequency, schedule_time, schedule_day, schedule_cron, is_active, sync_params, created_at, updated_at)
            VALUES
            (NULL, '{$schedule['sync_type']}', '{$schedule['frequency']}', $time, $day, $cron, 1, $params, NOW(), NOW())";

    if ($conn->query($sql) === TRUE) {
        $inserted++;
        $timeInfo = isset($schedule['schedule_cron']) ? $schedule['schedule_cron'] : ($schedule['schedule_time'] ?? 'N/A');
        echo "✅ {$schedule['sync_type']} - {$schedule['frequency']} ($timeInfo)\n";
    } else {
        echo "❌ Error: " . $conn->error . "\n";
    }
}

echo "\n============================================================\n";
echo "✅ $inserted Zeitpläne erstellt!\n\n";

// Zeige alle Zeitpläne gruppiert
echo "📋 SYNC-ZEITPLÄNE ÜBERSICHT:\n";
echo "============================================================\n\n";

echo "⚡ ALLE 5 MINUTEN (9:00-23:00) - Live-Daten (-7 bis +14 Tage):\n";
echo "------------------------------------------------------------\n";
$result = $conn->query("
    SELECT sync_type, schedule_cron, sync_params
    FROM sync_schedules
    WHERE frequency = 'custom' AND is_active = 1
    ORDER BY sync_type
");
while ($row = $result->fetch_assoc()) {
    $p = json_decode($row['sync_params'], true);
    $range = isset($p['date_from']) ? " [{$p['date_from']} bis {$p['date_to']}]" : '';
    echo "  • {$row['sync_type']}$range\n";
}

echo "\n📅 TÄGLICH:\n";
echo "------------------------------------------------------------\n";
$result = $conn->query("
    SELECT sync_type, schedule_time, sync_params
    FROM sync_schedules
    WHERE frequency = 'daily' AND is_active = 1
    ORDER BY schedule_time
");
while ($row = $result->fetch_assoc()) {
    echo "  • {$row['sync_type']} - {$row['schedule_time']} Uhr\n";
}

echo "\n📅 WÖCHENTLICH (Sonntag):\n";
echo "------------------------------------------------------------\n";
$result = $conn->query("
    SELECT sync_type, schedule_time
    FROM sync_schedules
    WHERE frequency = 'weekly' AND is_active = 1
    ORDER BY schedule_time
");
while ($row = $result->fetch_assoc()) {
    echo "  • {$row['sync_type']} - {$row['schedule_time']} Uhr\n";
}

echo "\n📅 MONATLICH (Sonntag):\n";
echo "------------------------------------------------------------\n";
$result = $conn->query("
    SELECT sync_type, schedule_time
    FROM sync_schedules
    WHERE frequency = 'monthly' AND is_active = 1
    ORDER BY schedule_time
");
while ($row = $result->fetch_assoc()) {
    echo "  • {$row['sync_type']} - {$row['schedule_time']} Uhr\n";
}

echo "\n💡 UPDATE-MODUS:\n";
echo "   - Alle Syncs verwenden 'upsert' (INSERT oder UPDATE)\n";
echo "   - Wenn Daten bereits existieren → UPDATE\n";
echo "   - Wenn neu → INSERT\n";

$conn->close();
