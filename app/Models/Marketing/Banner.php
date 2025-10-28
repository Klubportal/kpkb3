<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'link_url',
        'position',
        'display_order',
        'start_date',
        'end_date',
        'status',
        'click_count',
        'view_count',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function clubs()
    {
        return $this->belongsToMany(ClubExtended::class, 'club_banners', 'banner_id', 'club_id')
            ->withPivot('package_level', 'active_from', 'active_until', 'display_order')
            ->withTimestamps();
    }

    public function clubBanners()
    {
        return $this->hasMany(ClubBanner::class);
    }
}
