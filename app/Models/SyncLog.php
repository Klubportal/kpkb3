<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SyncLog extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'sync_type',
        'status',
        'records_processed',
        'records_inserted',
        'records_updated',
        'records_skipped',
        'records_failed',
        'total_records',
        'started_at',
        'completed_at',
        'duration_seconds',
        'error_message',
        'error_details',
        'sync_params',
        'sync_metadata',
    ];

    protected $casts = [
        'sync_params' => 'array',
        'sync_metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Start a new sync log entry
     */
    public static function startSync(string $syncType, ?string $tenantId = null, ?array $params = []): self
    {
        return self::create([
            'tenant_id' => $tenantId ?? 'landlord',
            'sync_type' => $syncType,
            'status' => 'running',
            'started_at' => Carbon::now(),
            'sync_params' => $params,
        ]);
    }

    /**
     * Complete a sync log entry with statistics
     */
    public function complete(array $stats = []): void
    {
        $completedAt = Carbon::now();

        // Reload fresh from DB to get proper datetime
        $this->refresh();

        // Calculate duration in seconds
        $duration = $this->started_at->diffInSeconds($completedAt);

        $this->update([
            'status' => $stats['status'] ?? 'success',
            'records_inserted' => $stats['inserted'] ?? 0,
            'records_updated' => $stats['updated'] ?? 0,
            'records_skipped' => $stats['skipped'] ?? 0,
            'records_failed' => $stats['failed'] ?? 0,
            'total_records' => $stats['total'] ?? 0,
            'records_processed' => ($stats['inserted'] ?? 0) + ($stats['updated'] ?? 0),
            'completed_at' => $completedAt,
            'duration_seconds' => $duration,
            'error_message' => $stats['error'] ?? null,
            'error_details' => $stats['error_details'] ?? null,
            'sync_metadata' => $stats['metadata'] ?? null,
        ]);
    }        /**
     * Mark sync as failed
     */
    public function fail(string $error, ?string $errorDetails = null): void
    {
        $completedAt = Carbon::now();

        // Reload fresh from DB
        $this->refresh();

        // Calculate duration in seconds
        $duration = $this->started_at->diffInSeconds($completedAt);

        $this->update([
            'status' => 'failed',
            'completed_at' => $completedAt,
            'duration_seconds' => $duration,
            'error_message' => $error,
            'error_details' => $errorDetails,
        ]);
    }
}

