<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public string $site_description;
    public ?string $logo;
    public ?string $favicon;
    public string $logo_height;
    public string $primary_color;
    public string $secondary_color;
    public string $font_family;
    public int $font_size;
    public string $contact_email;
    public ?string $phone;

    public static function group(): string
    {
        return 'general';
    }

    /**
     * Force GeneralSettings to always use the central database connection,
     * even when loaded in tenant context.
     */
    public static function repository(): string
    {
        return 'central_database';
    }
}
