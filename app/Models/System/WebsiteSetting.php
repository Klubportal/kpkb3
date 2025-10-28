<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $table = 'website_settings';

    protected $fillable = [
        'logo_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'font_family',
        'site_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get or create the single website settings record
     */
    public static function getSettings()
    {
        return self::first() ?? self::create();
    }

    /**
     * Update website settings
     */
    public static function updateSettings(array $data)
    {
        $settings = self::first() ?? new self();
        $settings->fill($data);
        $settings->save();
        return $settings;
    }
}
