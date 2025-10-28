<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ThemeSettings extends Settings
{
    public ?string $active_theme;           // z.B. 'default', 'blue_ocean', etc.
    public string $header_bg_color;
    public string $footer_bg_color;
    public string $text_color;
    public string $link_color;
    public string $button_style;
    public bool $dark_mode_enabled;
    public string $layout_style;             // 'boxed', 'full-width'

    // Zusätzliche Theme-Optionen
    public ?string $font_family;             // 'inter', 'roboto', 'poppins'
    public ?string $border_radius;           // 'none', 'sm', 'md', 'lg', 'full'
    public ?string $sidebar_width;           // 'narrow', 'normal', 'wide'

    public static function group(): string
    {
        return 'theme';
    }
}
