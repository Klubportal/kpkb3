<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactFormSubmission extends Model
{
    protected $fillable = [
        'club_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'ip_address',
        'status',
        'reply_message',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function reply($message)
    {
        $this->update([
            'reply_message' => $message,
            'replied_at' => now(),
            'status' => 'replied',
        ]);
    }

    public function markAsRead()
    {
        if ($this->status === 'new') {
            $this->update(['status' => 'read']);
        }
    }

    public function markAsSpam()
    {
        $this->update(['status' => 'spam']);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeUnreplied($query)
    {
        return $query->where('status', '!=', 'replied');
    }

    public function scopeFromClub($query, $clubId)
    {
        return $query->where('club_id', $clubId);
    }
}
