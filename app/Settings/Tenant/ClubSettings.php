<?php

namespace App\Settings\Tenant;

use Spatie\LaravelSettings\Settings;

class ClubSettings extends Settings
{
    // ⚽ Verein-Identität
    public string $club_name;
    public ?string $club_full_name;
    public ?string $club_slogan;
    public ?string $founded_year;

    // 🎨 Branding
    public ?string $logo;
    public ?string $logo_dark;  // Für Dark Mode
    public ?string $favicon;
    public ?string $header_image;

    // 🎨 Farben
    public string $primary_color;
    public string $secondary_color;
    public ?string $accent_color;

    // 📝 Typografie
    public string $font_family;
    public int $font_size;

    // 📧 Kontakt
    public ?string $contact_email;
    public ?string $phone;
    public ?string $address;
    public ?string $city;
    public ?string $postal_code;
    public ?string $country;

    // 🔗 Social Media
    public ?string $facebook_url;
    public ?string $instagram_url;
    public ?string $twitter_url;
    public ?string $youtube_url;
    public ?string $tiktok_url;

    // ⚙️ Weitere Einstellungen
    public bool $show_sponsors;
    public bool $show_news;
    public bool $show_calendar;
    public string $timezone;
    public string $locale;

    public static function group(): string
    {
        return 'club';
    }
}
