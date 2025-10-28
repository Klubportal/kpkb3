<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kpkb3';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "📅 Setting up sync schedules...\n";
echo "============================================================\n\n";

// Standard-Zeitpläne basierend auf Anforderungen
$schedules = [
    // Competitions - Sonntag um 2:00
    [
        'sync_type' => 'competitions',
        'frequency' => 'weekly',
        'schedule_time' => '02:00',
        'schedule_day' => 0, // Sonntag
        'sync_params' => null
    ],

    // Matches - Täglich 9:00-23:00 (stündlich), nur -7 bis +14 Tage
    [
        'sync_type' => 'matches',
        'frequency' => 'hourly',
        'schedule_time' => null,
        'schedule_day' => null,
        'sync_params' => json_encode([
            'hours' => '9-23',
            'date_from' => '-7 days',
            'date_to' => '+14 days'
        ])
    ],

    // Match Events - Täglich 9:00-23:00, -7 bis +14 Tage
    [
        'sync_type' => 'match_events',
        'frequency' => 'hourly',
        'schedule_time' => null,
        'schedule_day' => null,
        'sync_params' => json_encode([
            'hours' => '9-23',
            'date_from' => '-7 days',
            'date_to' => '+14 days'
        ])
    ],

    // Rankings - Täglich 9:00-23:00
    [
        'sync_type' => 'rankings',
        'frequency' => 'hourly',
        'schedule_time' => null,
        'schedule_day' => null,
        'sync_params' => json_encode([
            'hours' => '9-23'
        ])
    ],

    // Match Officials - Einmal täglich
    [
        'sync_type' => 'match_officials',
        'frequency' => 'daily',
        'schedule_time' => '03:00',
        'schedule_day' => null,
        'sync_params' => json_encode([
            'date_from' => '-7 days',
            'date_to' => '+14 days'
        ])
    ],

    // Team Officials - Einmal im Monat, Sonntag 2:00
    [
        'sync_type' => 'team_officials',
        'frequency' => 'monthly',
        'schedule_time' => '02:00',
        'schedule_day' => 0, // Sonntag
        'sync_params' => null
    ],

    // Match Team Officials - Einmal täglich
    [
        'sync_type' => 'match_team_officials',
        'frequency' => 'daily',
        'schedule_time' => '03:30',
        'schedule_day' => null,
        'sync_params' => json_encode([
            'date_from' => '-7 days',
            'date_to' => '+14 days'
        ])
    ],

    // Representatives - Einmal im Monat, Sonntag 2:00
    [
        'sync_type' => 'representatives',
        'frequency' => 'monthly',
        'schedule_time' => '02:00',
        'schedule_day' => 0, // Sonntag
        'sync_params' => null
    ],

    // Top Scorers - Täglich
    [
        'sync_type' => 'top_scorers',
        'frequency' => 'daily',
        'schedule_time' => '04:00',
        'schedule_day' => null,
        'sync_params' => null
    ]
];

$inserted = 0;
foreach ($schedules as $schedule) {
    $params = $schedule['sync_params'] ? "'" . $schedule['sync_params'] . "'" : 'NULL';
    $time = $schedule['schedule_time'] ? "'" . $schedule['schedule_time'] . "'" : 'NULL';
    $day = $schedule['schedule_day'] !== null ? $schedule['schedule_day'] : 'NULL';

    $sql = "INSERT INTO sync_schedules
            (tenant_id, sync_type, frequency, schedule_time, schedule_day, is_active, sync_params, created_at, updated_at)
            VALUES
            (NULL, '{$schedule['sync_type']}', '{$schedule['frequency']}', $time, $day, 1, $params, NOW(), NOW())";

    if ($conn->query($sql) === TRUE) {
        $inserted++;
        $timeInfo = $schedule['schedule_time'] ?: 'stündlich';
        $dayInfo = $schedule['schedule_day'] !== null ? ' (Sonntag)' : '';
        echo "✅ {$schedule['sync_type']} - {$schedule['frequency']} um $timeInfo$dayInfo\n";
    } else {
        echo "❌ Error: " . $conn->error . "\n";
    }
}

echo "\n============================================================\n";
echo "✅ $inserted Zeitpläne erstellt!\n\n";

// Zeige alle Zeitpläne
echo "📋 Übersicht aller Sync-Zeitpläne:\n";
echo "------------------------------------------------------------\n";
$result = $conn->query("
    SELECT sync_type, frequency, schedule_time, schedule_day, sync_params
    FROM sync_schedules
    WHERE is_active = 1
    ORDER BY
        CASE frequency
            WHEN 'hourly' THEN 1
            WHEN 'daily' THEN 2
            WHEN 'weekly' THEN 3
            WHEN 'monthly' THEN 4
        END,
        sync_type
");

while ($row = $result->fetch_assoc()) {
    $time = $row['schedule_time'] ?: 'jede Stunde';
    $day = '';
    if ($row['schedule_day'] !== null) {
        $days = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
        $day = ' (' . $days[$row['schedule_day']] . ')';
    }

    $params = '';
    if ($row['sync_params']) {
        $p = json_decode($row['sync_params'], true);
        if (isset($p['hours'])) $params .= " [{$p['hours']} Uhr]";
        if (isset($p['date_from'])) $params .= " [{$p['date_from']} bis {$p['date_to']}]";
    }

    echo sprintf("%-25s | %-10s | %s%s%s\n",
        $row['sync_type'],
        $row['frequency'],
        $time,
        $day,
        $params
    );
}

$conn->close();
