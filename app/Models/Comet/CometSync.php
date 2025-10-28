<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;

class CometSync extends Model
{
    protected $connection = 'tenant';

    protected $table = 'comet_syncs';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'records_affected',
        'sync_data',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'sync_data' => 'array',
        'records_affected' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
