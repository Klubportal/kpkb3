<?php

namespace App\Services;

use App\Settings\ThemeSettings;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelSettings\Exceptions\MissingSettings;

/**
 * Service für dynamische Frontend-Theme-Anwendung
 * Konvertiert Backend ThemeSettings in Frontend CSS Variables
 */
class FrontendThemeService
{
    protected ?ThemeSettings $settings = null;

    public function __construct()
    {
        try {
            $this->settings = app(ThemeSettings::class);
        } catch (MissingSettings $e) {
            // Settings existieren nicht - verwende Defaults
            $this->settings = null;
        } catch (\Exception $e) {
            // Andere Fehler - verwende Defaults
            $this->settings = null;
        }
    }

    /**
     * Generiert CSS Custom Properties (CSS Variables) basierend auf ThemeSettings
     */
    public function generateCssVariables(): string
    {
        // Cache-Key mit Tenant-ID für Multi-Tenancy
        $cacheKey = 'frontend_theme_css_' . (tenancy()->tenant?->getTenantKey() ?? 'central');

        try {
            return Cache::remember($cacheKey, 3600, function () {
                return $this->buildCssVariables();
            });
        } catch (\BadMethodCallException $e) {
            // Fallback wenn Cache-Driver kein Tagging unterstützt
            return $this->buildCssVariables();
        }
    }

    /**
     * Baut die CSS Variables String
     */
    protected function buildCssVariables(): string
    {
        $vars = [
            // Farben
            '--theme-header-bg' => $this->settings?->header_bg_color ?? '#dc2626',
            '--theme-footer-bg' => $this->settings?->footer_bg_color ?? '#1f2937',
            '--theme-text' => $this->settings?->text_color ?? '#1f2937',
            '--theme-link' => $this->settings?->link_color ?? '#2563eb',

            // Design
            '--theme-border-radius' => $this->getBorderRadiusValue(),
            '--theme-sidebar-width' => $this->getSidebarWidthValue(),

            // Schriftart
            '--theme-font-family' => $this->getFontFamilyValue(),
        ];

        $css = ':root {' . PHP_EOL;
        foreach ($vars as $key => $value) {
            $css .= "    {$key}: {$value};" . PHP_EOL;
        }
        $css .= '}';

        return $css;
    }

    /**
     * Gibt die DaisyUI Theme-Konfiguration zurück
     */
    public function getDaisyUITheme(): array
    {
        return [
            'primary' => $this->settings?->header_bg_color ?? '#dc2626',
            'secondary' => $this->adjustColorBrightness($this->settings?->header_bg_color ?? '#dc2626', -20),
            'accent' => $this->settings?->link_color ?? '#2563eb',
            'neutral' => '#1f2937',
            'base-100' => '#ffffff',
            'base-200' => '#f3f4f6',
            'base-300' => '#e5e7eb',
            'info' => '#3abff8',
            'success' => '#36d399',
            'warning' => '#fbbd23',
            'error' => '#f87272',
        ];
    }

    /**
     * Gibt den Button-Stil zurück
     */
    public function getButtonStyle(): string
    {
        return match($this->settings?->button_style ?? 'rounded') {
            'square' => 'rounded-none',
            'rounded' => 'rounded-lg',
            'pill' => 'rounded-full',
            default => 'rounded-lg',
        };
    }

    /**
     * Gibt das Layout zurück
     */
    public function getLayoutClass(): string
    {
        return match($this->settings?->layout_style ?? 'full-width') {
            'boxed' => 'max-w-7xl mx-auto',
            'full-width' => 'w-full',
            default => 'max-w-7xl mx-auto',
        };
    }

    /**
     * Gibt zurück ob Dark Mode aktiv ist
     */
    public function isDarkMode(): bool
    {
        return $this->settings?->dark_mode_enabled ?? false;
    }

    /**
     * Konvertiert Border Radius Setting zu CSS-Wert
     */
    protected function getBorderRadiusValue(): string
    {
        return match($this->settings?->border_radius ?? 'md') {
            'none' => '0px',
            'sm' => '4px',
            'md' => '8px',
            'lg' => '12px',
            'xl' => '16px',
            default => '8px',
        };
    }

    /**
     * Konvertiert Sidebar Width Setting zu CSS-Wert
     */
    protected function getSidebarWidthValue(): string
    {
        return match($this->settings?->sidebar_width ?? 'normal') {
            'narrow' => '200px',
            'normal' => '250px',
            'wide' => '300px',
            default => '250px',
        };
    }

    /**
     * Konvertiert Font Family Setting zu CSS Font Stack
     */
    protected function getFontFamilyValue(): string
    {
        return match($this->settings?->font_family ?? 'inter') {
            'inter' => "'Inter', ui-sans-serif, system-ui, sans-serif",
            'roboto' => "'Roboto', ui-sans-serif, system-ui, sans-serif",
            'poppins' => "'Poppins', ui-sans-serif, system-ui, sans-serif",
            'open_sans' => "'Open Sans', ui-sans-serif, system-ui, sans-serif",
            'lato' => "'Lato', ui-sans-serif, system-ui, sans-serif",
            'montserrat' => "'Montserrat', ui-sans-serif, system-ui, sans-serif",
            default => "'Inter', ui-sans-serif, system-ui, sans-serif",
        };
    }

    /**
     * Passt Helligkeit einer Hex-Farbe an
     */
    protected function adjustColorBrightness(string $hex, int $percent): string
    {
        // Entferne # falls vorhanden
        $hex = ltrim($hex, '#');

        // Konvertiere zu RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Passe Helligkeit an
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));

        // Zurück zu Hex
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Gibt alle Theme-Daten für Frontend zurück
     */
    public function getThemeData(): array
    {
        return [
            'css_variables' => $this->generateCssVariables(),
            'daisyui_theme' => $this->getDaisyUITheme(),
            'button_style' => $this->getButtonStyle(),
            'layout_class' => $this->getLayoutClass(),
            'dark_mode' => $this->isDarkMode(),
            'font_family' => $this->settings?->font_family ?? 'inter',
            'active_theme' => $this->settings?->active_theme ?? 'default',
        ];
    }

    /**
     * Cache löschen
     */
    public static function clearCache(): void
    {
        try {
            // Versuche Cache mit Tenant-Key zu löschen
            $cacheKey = 'frontend_theme_css_' . (tenancy()->tenant?->getTenantKey() ?? 'central');
            Cache::forget($cacheKey);

            // Auch den alten Key löschen (Fallback)
            Cache::forget('frontend_theme_css');
        } catch (\Exception $e) {
            // Silent fail wenn Cache nicht verfügbar
        }
    }
}
