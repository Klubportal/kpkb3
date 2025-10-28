<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SocialMediaSettings extends Settings
{
    public ?string $facebook_url;
    public ?string $instagram_url;
    public ?string $twitter_url;
    public ?string $youtube_url;
    public ?string $linkedin_url;
    public ?string $tiktok_url;

    public static function group(): string
    {
        return 'social_media';
    }
}
