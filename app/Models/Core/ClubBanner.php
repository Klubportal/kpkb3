<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class ClubBanner extends Model
{
    protected $fillable = [
        'club_id',
        'banner_id',
        'package_level',
        'active_from',
        'active_until',
        'display_order',
    ];

    protected $casts = [
        'active_from' => 'datetime',
        'active_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(ClubExtended::class, 'club_id');
    }

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }
}
