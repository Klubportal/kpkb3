<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupportTicket extends Model
{
    use HasFactory;

    protected $table = 'platform_support_tickets';

    protected $fillable = [
        'club_id',
        'created_by',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'category',
        'status',
        'resolution',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    /**
     * Relationships
     */
    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function creator()
    {
        return $this->belongsTo(PlatformUser::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(PlatformUser::class, 'assigned_to');
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_CRITICAL]);
    }

    /**
     * Methods
     */
    public function isOpen()
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function close($resolution = null)
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
            'resolution' => $resolution,
            'resolved_at' => now(),
        ]);
    }
}
