<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sponsor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'logo_url',
        'website_url',
        'email',
        'phone',
        'status',
        'display_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function clubs()
    {
        return $this->belongsToMany(ClubExtended::class, 'club_sponsors', 'sponsor_id', 'club_id')
            ->withPivot('package_level', 'display_order')
            ->withTimestamps();
    }

    public function clubSponsors()
    {
        return $this->hasMany(ClubSponsor::class);
    }
}
