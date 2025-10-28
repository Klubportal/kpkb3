<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = [
        'name',
        'label',
        'active',
        'published',
        'user_custom_group',
        'slug',
        'gender',
        'age_category',
        'age_category_name',
        'comet_competition_type',
        'order',
        'featured_image',
    ];

    protected $casts = [
        'active' => 'boolean',
        'published' => 'boolean',
        'user_custom_group' => 'boolean',
    ];

    /**
     * Get the players in this group
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(
            CometPlayer::class,
            'player_group',
            'group_id',
            'player_id'
        )->withPivot([
            'position',
            'jersey_number',
            'is_captain',
            'status',
            'joined_at',
            'left_at',
        ])->withTimestamps();
    }

    /**
     * Scope to only active groups
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to only published groups
     */
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
