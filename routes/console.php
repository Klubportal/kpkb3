<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================================
// COMET API SYNC SCHEDULER
// ============================================================================

// Sync all Comet data every 5 minutes (Landlord Database)
Schedule::command('comet:sync-all')
    ->everyFiveMinutes()
    ->timezone('Europe/Zagreb')
    ->name('Comet Full Sync')
    ->description('Sync all matches, rankings and top scorers from Comet API every 5 minutes');

// Sync all tenant databases every 10 minutes (after landlord sync)
Schedule::command('tenant:sync-comet --all')
    ->everyTenMinutes()
    ->timezone('Europe/Zagreb')
    ->name('Tenant Data Sync')
    ->description('Sync Comet data from landlord to all tenant databases');

// Alternative: Einzelne Syncs gestaffelt (falls gewünscht)
// Schedule::command('comet:sync-matches')->everyFiveMinutes();
// Schedule::command('comet:sync-rankings')->everyFiveMinutes()->delay(1); // 1 Minute verzögert
// Schedule::command('comet:sync-topscorers')->everyFiveMinutes()->delay(2); // 2 Minuten verzögert
