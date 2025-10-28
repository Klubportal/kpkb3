<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncLog;
use App\Services\CometSyncService;
use Carbon\Carbon;

abstract class BaseSyncCommand extends Command
{
    protected SyncLog $syncLog;
    protected CometSyncService $syncService;
    protected float $startTime;

    /**
     * Start sync logging
     */
    protected function startSync(string $syncType, ?array $params = []): void
    {
        $this->startTime = microtime(true);

        $this->syncLog = SyncLog::startSync($syncType, null, $params);

        $this->info("🚀 STARTING SYNC: {$syncType}");
        $this->line(str_repeat('=', 70));
        $this->newLine();
    }

    /**
     * Complete sync successfully
     */
    protected function completeSync(array $result): int
    {
        $duration = round(microtime(true) - $this->startTime, 2);

        $this->syncLog->complete([
            'inserted' => $result['inserted'] ?? 0,
            'updated' => $result['updated'] ?? 0,
            'skipped' => $result['skipped'] ?? 0,
            'failed' => $result['errors'] ?? 0,
            'total' => $result['total'] ?? 0,
            'metadata' => array_merge($result['metadata'] ?? [], [
                'duration' => $duration,
                'completed_at' => now()->toIso8601String(),
            ]),
        ]);

        $this->newLine();
        $this->line(str_repeat('=', 70));
        $this->info("✅ SYNC COMPLETED in {$duration}s");
        $this->newLine();

        // Display stats table
        $this->displayStats($result);

        return $result['errors'] > 0 ? 1 : 0;
    }

    /**
     * Handle sync failure
     */
    protected function failSync(\Exception $e): int
    {
        $duration = round(microtime(true) - $this->startTime, 2);

        $this->syncLog->fail($e->getMessage(), $e->getTraceAsString());

        $this->newLine();
        $this->line(str_repeat('=', 70));
        $this->error("❌ SYNC FAILED after {$duration}s");
        $this->error("Error: {$e->getMessage()}");
        $this->newLine();

        if ($this->output->isVerbose()) {
            $this->line($e->getTraceAsString());
        }

        return 1;
    }

    /**
     * Display statistics table
     */
    protected function displayStats(array $result): void
    {
        $stats = [
            ['Metric', 'Count'],
        ];

        if (isset($result['processed'])) {
            $stats[] = ['Processed', number_format($result['processed'])];
        }

        if (isset($result['total'])) {
            $stats[] = ['Total Records', number_format($result['total'])];
        }

        $stats[] = ['Inserted', number_format($result['inserted'] ?? 0)];
        $stats[] = ['Updated', number_format($result['updated'] ?? 0)];
        $stats[] = ['Skipped', number_format($result['skipped'] ?? 0)];

        if (isset($result['errors']) && $result['errors'] > 0) {
            $stats[] = ['Errors', number_format($result['errors'])];
        }

        $this->table(
            array_shift($stats),
            $stats
        );
    }

    /**
     * Create progress bar
     */
    protected function createProgressBar(int $max): \Symfony\Component\Console\Helper\ProgressBar
    {
        $bar = $this->output->createProgressBar($max);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
        return $bar;
    }

    /**
     * Get sync service instance
     */
    protected function getSyncService(): CometSyncService
    {
        if (!isset($this->syncService)) {
            $this->syncService = app(CometSyncService::class);
        }
        return $this->syncService;
    }
}
