<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvertisingBanner extends Model
{
    use SoftDeletes;

    protected $table = 'advertising_banners';

    protected $fillable = [
        'banner_name',
        'image_path',
        'link_url',
        'description',
        'size',
        'is_active',
        'display_count',
        'click_count',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_count' => 'integer',
        'click_count' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope to get only active banners
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the full URL to the banner image
     */
    public function getImageUrl()
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Increment display count
     */
    public function recordDisplay()
    {
        $this->increment('display_count');
    }

    /**
     * Increment click count
     */
    public function recordClick()
    {
        $this->increment('click_count');
    }

    /**
     * Get click-through rate (CTR)
     */
    public function getClickThroughRate()
    {
        if ($this->display_count == 0) return 0;
        return round(($this->click_count / $this->display_count) * 100, 2);
    }
}
