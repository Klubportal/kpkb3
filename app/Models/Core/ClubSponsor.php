<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class ClubSponsor extends Model
{
    protected $fillable = [
        'club_id',
        'sponsor_id',
        'package_level',
        'display_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(ClubExtended::class, 'club_id');
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
