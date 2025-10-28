<?php

namespace App\Services;

/**
 * Theme-Service mit vorgefertigten Design-Themes
 *
 * Verfügbare Themes:
 * - default (Rot/Grau - Standard Klubportal)
 * - blue_ocean (Blau/Türkis - Modern & Professional)
 * - green_forest (Grün/Braun - Natur & Sport)
 * - purple_royal (Lila/Gold - Premium & Elegant)
 * - orange_energy (Orange/Gelb - Energetisch & Dynamisch)
 * - dark_mode (Dunkel/Neon - Modern Dark Theme)
 * - classic_navy (Navy/Beige - Klassisch & Seriös)
 * - sport_red (Rot/Schwarz - Sportlich & Kraftvoll)
 */
class ThemeService
{
    /**
     * Alle verfügbaren Themes
     */
    public static function getAvailableThemes(): array
    {
        return [
            'default' => [
                'name' => 'Standard (Klubportal)',
                'description' => 'Das Standard-Theme mit Rot und Grau',
                'primary_color' => '#dc2626',      // Rot
                'secondary_color' => '#737373',    // Grau
                'accent_color' => '#f59e0b',       // Amber
                'text_color' => '#1f2937',         // Dunkelgrau
                'link_color' => '#dc2626',         // Rot
                'header_bg' => '#ffffff',          // Weiß
                'footer_bg' => '#1f2937',          // Dunkelgrau
                'preview_image' => 'default.png',
            ],

            'blue_ocean' => [
                'name' => 'Blauer Ozean',
                'description' => 'Modern und professionell mit Blau- und Türkistönen',
                'primary_color' => '#0ea5e9',      // Sky Blue
                'secondary_color' => '#0284c7',    // Light Blue
                'accent_color' => '#06b6d4',       // Cyan
                'text_color' => '#0f172a',         // Slate
                'link_color' => '#0ea5e9',         // Sky Blue
                'header_bg' => '#f0f9ff',          // Sky 50
                'footer_bg' => '#0c4a6e',          // Sky 900
                'preview_image' => 'blue_ocean.png',
            ],

            'green_forest' => [
                'name' => 'Grüner Wald',
                'description' => 'Natürlich und sportlich mit Grün- und Brauntönen',
                'primary_color' => '#16a34a',      // Green
                'secondary_color' => '#059669',    // Emerald
                'accent_color' => '#84cc16',       // Lime
                'text_color' => '#1c1917',         // Stone
                'link_color' => '#16a34a',         // Green
                'header_bg' => '#f0fdf4',          // Green 50
                'footer_bg' => '#14532d',          // Green 900
                'preview_image' => 'green_forest.png',
            ],

            'purple_royal' => [
                'name' => 'Königliches Lila',
                'description' => 'Premium und elegant mit Lila- und Goldtönen',
                'primary_color' => '#9333ea',      // Purple
                'secondary_color' => '#7c3aed',    // Violet
                'accent_color' => '#eab308',       // Yellow
                'text_color' => '#1c1917',         // Stone
                'link_color' => '#9333ea',         // Purple
                'header_bg' => '#faf5ff',          // Purple 50
                'footer_bg' => '#581c87',          // Purple 900
                'preview_image' => 'purple_royal.png',
            ],

            'orange_energy' => [
                'name' => 'Orange Energie',
                'description' => 'Energetisch und dynamisch mit Orange- und Gelbtönen',
                'primary_color' => '#ea580c',      // Orange
                'secondary_color' => '#f97316',    // Orange
                'accent_color' => '#fbbf24',       // Amber
                'text_color' => '#1c1917',         // Stone
                'link_color' => '#ea580c',         // Orange
                'header_bg' => '#fff7ed',          // Orange 50
                'footer_bg' => '#7c2d12',          // Orange 900
                'preview_image' => 'orange_energy.png',
            ],

            'dark_mode' => [
                'name' => 'Dark Mode',
                'description' => 'Modernes Dark Theme mit Neonakzenten',
                'primary_color' => '#8b5cf6',      // Violet
                'secondary_color' => '#6366f1',    // Indigo
                'accent_color' => '#06b6d4',       // Cyan
                'text_color' => '#f9fafb',         // Gray 50
                'link_color' => '#8b5cf6',         // Violet
                'header_bg' => '#111827',          // Gray 900
                'footer_bg' => '#030712',          // Gray 950
                'preview_image' => 'dark_mode.png',
            ],

            'classic_navy' => [
                'name' => 'Klassisch Navy',
                'description' => 'Klassisch und seriös mit Navy- und Beigetönen',
                'primary_color' => '#1e40af',      // Blue
                'secondary_color' => '#1e3a8a',    // Blue
                'accent_color' => '#d97706',       // Amber
                'text_color' => '#1f2937',         // Gray
                'link_color' => '#1e40af',         // Blue
                'header_bg' => '#eff6ff',          // Blue 50
                'footer_bg' => '#1e3a8a',          // Blue 900
                'preview_image' => 'classic_navy.png',
            ],

            'sport_red' => [
                'name' => 'Sport Rot',
                'description' => 'Sportlich und kraftvoll mit Rot- und Schwarztönen',
                'primary_color' => '#dc2626',      // Red
                'secondary_color' => '#b91c1c',    // Red
                'accent_color' => '#fbbf24',       // Amber
                'text_color' => '#1f2937',         // Gray
                'link_color' => '#dc2626',         // Red
                'header_bg' => '#fef2f2',          // Red 50
                'footer_bg' => '#1f2937',          // Gray 800
                'preview_image' => 'sport_red.png',
            ],

            'teal_fresh' => [
                'name' => 'Frisches Teal',
                'description' => 'Frisch und modern mit Teal- und Mintönen',
                'primary_color' => '#14b8a6',      // Teal
                'secondary_color' => '#0d9488',    // Teal
                'accent_color' => '#34d399',       // Emerald
                'text_color' => '#1f2937',         // Gray
                'link_color' => '#14b8a6',         // Teal
                'header_bg' => '#f0fdfa',          // Teal 50
                'footer_bg' => '#134e4a',          // Teal 900
                'preview_image' => 'teal_fresh.png',
            ],

            'rose_elegant' => [
                'name' => 'Elegantes Rosa',
                'description' => 'Elegant und modern mit Rosa- und Grautönen',
                'primary_color' => '#f43f5e',      // Rose
                'secondary_color' => '#e11d48',    // Rose
                'accent_color' => '#ec4899',       // Pink
                'text_color' => '#1f2937',         // Gray
                'link_color' => '#f43f5e',         // Rose
                'header_bg' => '#fff1f2',          // Rose 50
                'footer_bg' => '#4c0519',          // Rose 950
                'preview_image' => 'rose_elegant.png',
            ],
        ];
    }

    /**
     * Hole ein spezifisches Theme
     */
    public static function getTheme(string $themeKey): ?array
    {
        $themes = self::getAvailableThemes();
        return $themes[$themeKey] ?? null;
    }

    /**
     * Wende ein Theme an
     */
    public static function applyTheme(string $themeKey, \App\Settings\ThemeSettings $themeSettings): void
    {
        $theme = self::getTheme($themeKey);

        if (!$theme) {
            return;
        }

        // Update ThemeSettings mit Theme-Werten
        $themeSettings->header_bg_color = $theme['header_bg'];
        $themeSettings->footer_bg_color = $theme['footer_bg'];
        $themeSettings->text_color = $theme['text_color'];
        $themeSettings->link_color = $theme['link_color'];

        // Dark Mode für dark_mode Theme
        $themeSettings->dark_mode_enabled = ($themeKey === 'dark_mode');

        $themeSettings->save();
    }

    /**
     * Themes für Filament-Auswahl formatieren
     */
    public static function getThemesForSelect(): array
    {
        $themes = self::getAvailableThemes();
        $options = [];

        foreach ($themes as $key => $theme) {
            $options[$key] = $theme['name'] . ' - ' . $theme['description'];
        }

        return $options;
    }

    /**
     * Theme-Vorschau als HTML generieren
     */
    public static function getThemePreviewHtml(string $themeKey): string
    {
        $theme = self::getTheme($themeKey);

        if (!$theme) {
            return '';
        }

        return <<<HTML
        <div class="space-y-2 p-4 border rounded-lg">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded" style="background-color: {$theme['primary_color']}"></div>
                <span class="text-sm">Primary</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded" style="background-color: {$theme['secondary_color']}"></div>
                <span class="text-sm">Secondary</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded" style="background-color: {$theme['accent_color']}"></div>
                <span class="text-sm">Accent</span>
            </div>
        </div>
HTML;
    }
}
