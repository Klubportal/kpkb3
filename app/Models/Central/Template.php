<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'preview_image',
        'features',
        'colors',
        'layout_path',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'colors' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenants()
    {
        return $this->hasMany(\App\Models\Tenant::class, 'template_id');
    }

    public static function getDefault()
    {
        return static::where('is_default', true)->first()
            ?? static::where('is_active', true)->orderBy('sort_order')->first();
    }

    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get color value by key
     */
    public function getColor(string $key, string $default = '#000000'): string
    {
        return $this->colors[$key] ?? $default;
    }

    /**
     * Check if template has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get layout path for this template
     */
    public function getLayoutPath(): string
    {
        return $this->layout_path ?? 'layouts.frontend';
    }
}
