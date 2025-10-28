<?php

// MASTER SYNC RUNNER - Runs all Comet syncs in optimal order
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         COMET DATA SYNC - MASTER RUNNER                      ║\n";
echo "║         All syncs with upsert_if_changed optimization        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$startTime = microtime(true);

$syncs = [
    ['name' => 'Matches', 'file' => 'sync_matches.php', 'priority' => 1],
    ['name' => 'Rankings', 'file' => 'sync_rankings.php', 'priority' => 2],
    ['name' => 'Top Scorers', 'file' => 'sync_top_scorers.php', 'priority' => 3],
    ['name' => 'Match Team Officials', 'file' => 'sync_match_team_officials.php', 'priority' => 4],
];

$results = [];

foreach ($syncs as $sync) {
    echo "\n┌" . str_repeat("─", 58) . "┐\n";
    echo "│ 🔄 RUNNING: {$sync['name']}" . str_repeat(" ", 58 - 13 - strlen($sync['name'])) . "│\n";
    echo "└" . str_repeat("─", 58) . "┘\n\n";
    
    $syncStart = microtime(true);
    
    // Execute sync script
    $output = [];
    $returnCode = 0;
    exec("php {$sync['file']} 2>&1", $output, $returnCode);
    
    $syncEnd = microtime(true);
    $duration = round($syncEnd - $syncStart, 2);
    
    // Parse output for statistics
    $inserted = 0;
    $updated = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($output as $line) {
        if (preg_match('/Inserted:\s*(\d+)/', $line, $matches)) $inserted = (int)$matches[1];
        if (preg_match('/Updated:\s*(\d+)/', $line, $matches)) $updated = (int)$matches[1];
        if (preg_match('/Skipped.*?:\s*(\d+)/', $line, $matches)) $skipped = (int)$matches[1];
        if (preg_match('/Errors:\s*(\d+)/', $line, $matches)) $errors = (int)$matches[1];
    }
    
    $results[] = [
        'name' => $sync['name'],
        'duration' => $duration,
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'errors' => $errors,
        'success' => $returnCode === 0
    ];
    
    $status = $returnCode === 0 ? '✅' : '❌';
    echo "{$status} Completed in {$duration}s (I:{$inserted} U:{$updated} S:{$skipped} E:{$errors})\n";
}

$endTime = microtime(true);
$totalDuration = round($endTime - $startTime, 2);

echo "\n\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                     SYNC SUMMARY                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$totalInserted = 0;
$totalUpdated = 0;
$totalSkipped = 0;
$totalErrors = 0;

foreach ($results as $result) {
    $totalInserted += $result['inserted'];
    $totalUpdated += $result['updated'];
    $totalSkipped += $result['skipped'];
    $totalErrors += $result['errors'];
    
    $status = $result['success'] ? '✅' : '❌';
    printf("%s %-25s | %6.2fs | I:%4d U:%4d S:%4d E:%4d\n", 
        $status,
        $result['name'],
        $result['duration'],
        $result['inserted'],
        $result['updated'],
        $result['skipped'],
        $result['errors']
    );
}

echo "\n" . str_repeat("─", 62) . "\n";
printf("%-25s | %6.2fs | I:%4d U:%4d S:%4d E:%4d\n", 
    'TOTAL',
    $totalDuration,
    $totalInserted,
    $totalUpdated,
    $totalSkipped,
    $totalErrors
);

$totalRecords = $totalInserted + $totalUpdated + $totalSkipped;
$skipPercentage = $totalRecords > 0 ? round(($totalSkipped / $totalRecords) * 100, 1) : 0;

echo "\n📊 Efficiency: {$skipPercentage}% of records were unchanged (skipped DB writes)\n";
echo "⏱️  Total execution time: {$totalDuration}s\n";
echo "✅ Sync completed at: " . date('Y-m-d H:i:s') . "\n\n";

?>
