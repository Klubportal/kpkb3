<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class TemplateSetting extends Model
{
    protected $fillable = [
        'website_name',
        'club_fifa_id',
        'slogan',
        'logo',
        'logo_height',
        'primary_color',
        'secondary_color',
        'accent_color',
        'header_bg_color',
        'header_text_color',
        'badge_bg_color',
        'badge_text_color',
        'hero_bg_color',
        'hero_text_color',
        'footer_bg_color',
        'footer_text_color',
        'text_color',
        'show_logo',
        'sticky_header',
        'header_style',
        'footer_about',
        'footer_email',
        'footer_phone',
        'footer_address',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'youtube_url',
        'tiktok_url',
        'show_next_match',
        'show_last_results',
        'show_standings',
        'show_top_scorers',
        'show_news',
        'news_count',
        'enable_dark_mode',
        'enable_animations',
        'google_analytics_id',
    ];

    protected $casts = [
        'club_fifa_id' => 'integer',
        'logo_height' => 'integer',
        'show_logo' => 'boolean',
        'sticky_header' => 'boolean',
        'show_next_match' => 'boolean',
        'show_last_results' => 'boolean',
        'show_standings' => 'boolean',
        'show_top_scorers' => 'boolean',
        'show_news' => 'boolean',
        'news_count' => 'integer',
        'enable_dark_mode' => 'boolean',
        'enable_animations' => 'boolean',
    ];

    public static function current()
    {
        return static::first() ?? static::create([]);
    }

    /**
     * Get the logo URL for display in Filament and frontend
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }

        // Return relative URL that works for tenant subdomains
        return '/storage/' . $this->logo;
    }

    public function getSocialMediaLinksAttribute()
    {
        return collect([
            'facebook' => $this->facebook_url,
            'instagram' => $this->instagram_url,
            'twitter' => $this->twitter_url,
            'youtube' => $this->youtube_url,
            'tiktok' => $this->tiktok_url,
        ])->filter()->toArray();
    }
}
