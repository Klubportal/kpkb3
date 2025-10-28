<?php

if (!function_exists('get_available_locales')) {
    function get_available_locales(): array
    {
        return explode(',', config('app.available_locales', 'de,en'));
    }
}

if (!function_exists('get_locale_label')) {
    function get_locale_label(string $locale): string
    {
        $labels = [
            'de' => 'Deutsch',
            'en' => 'English',
            'hr' => 'Hrvatski',
            'bs' => 'Bosanski',
            'sr_Latn' => 'Srpski',
            'es' => 'Español',
            'fr' => 'Français',
            'it' => 'Italiano',
            'pt' => 'Português',
            'tr' => 'Türkçe',
        ];

        return $labels[$locale] ?? $locale;
    }
}
