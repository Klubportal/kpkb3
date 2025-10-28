<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncLog;

class SyncCometAll extends Command
{
    protected $signature = 'comet:sync-all';
    protected $description = 'Sync all Comet data (matches, rankings, top scorers)';

    public function handle()
    {
        // Start sync log
        $syncLog = SyncLog::startSync('comet_all');

        try {
            $this->info('🚀 SYNCING ALL COMET DATA');
            $this->line(str_repeat('=', 70));
            $this->newLine();

            $startTime = microtime(true);

            // Sync matches
            $this->call('comet:sync-matches');
            $this->newLine();

            // Sync rankings
            $this->call('comet:sync-rankings');
            $this->newLine();

            // Sync top scorers
            $this->call('comet:sync-topscorers');
            $this->newLine();

            $duration = round(microtime(true) - $startTime, 2);

            $this->line(str_repeat('=', 70));
            $this->info("✅ ALL SYNCS COMPLETED in {$duration}s");
            $this->newLine();

            // Complete sync log
            $syncLog->complete([
                'status' => 'success',
                'metadata' => [
                    'duration' => $duration,
                    'syncs' => ['matches', 'rankings', 'top_scorers'],
                ],
            ]);

            return 0;

        } catch (\Exception $e) {
            $syncLog->fail($e->getMessage(), $e->getTraceAsString());
            $this->error('Sync failed: ' . $e->getMessage());
            return 1;
        }
    }
}
