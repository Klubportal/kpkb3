<?php

$pdo = new PDO('mysql:host=localhost;dbname=kpkb3', 'root', '');
$rows = $pdo->query('SELECT sync_type, frequency, schedule_cron, sync_params FROM sync_schedules ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

echo "Scheduled Sync Jobs:\n";
echo str_repeat("=", 100) . "\n";
foreach($rows as $r) {
    echo sprintf("%-30s | %-10s | %-20s | %s\n",
        $r['sync_type'],
        $r['frequency'],
        $r['schedule_cron'] ?? $r['schedule_time'] ?? '-',
        substr($r['sync_params'] ?? '', 0, 40)
    );
}

?>
