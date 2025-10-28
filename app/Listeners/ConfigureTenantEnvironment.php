<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Events\TenancyInitialized;

/**
 * 🎛️ TENANT CONFIGURATION LISTENER
 *
 * Wird automatisch ausgelöst wenn ein Tenant initialisiert wird.
 * Überschreibt Laravel Config-Werte basierend auf Tenant-Settings.
 *
 * Registriert in: app/Providers/TenancyServiceProvider.php
 */
class ConfigureTenantEnvironment
{
    /**
     * Handle the event.
     */
    public function handle(TenancyInitialized $event): void
    {
        $tenant = $event->tenancy->tenant;

        // 📧 Mail Configuration
        if ($tenant->email ?? null) {
            Config::set('mail.from.address', $tenant->email);
            Config::set('mail.from.name', $tenant->name ?? config('app.name'));
        }

        // 🏢 App Configuration
        if ($tenant->name ?? null) {
            Config::set('app.name', $tenant->name);
        }

        // 🌐 URL Configuration
        if ($domain = $tenant->domains->first()) {
            Config::set('app.url', 'http://' . $domain->domain);
        }

        // 🎨 Theme Configuration (falls vorhanden)
        $settings = $this->getTenantSettings();

        if ($settings) {
            if (isset($settings['primary_color'])) {
                Config::set('filament.theme.primary_color', $settings['primary_color']);
            }

            if (isset($settings['logo'])) {
                Config::set('filament.brand.logo', $settings['logo']);
            }

            if (isset($settings['timezone'])) {
                Config::set('app.timezone', $settings['timezone']);
            }

            if (isset($settings['locale'])) {
                Config::set('app.locale', $settings['locale']);
            }
        }

        // 💾 Cache Configuration (tenant-specific prefix)
        Config::set('cache.prefix', 'tenant_' . $tenant->id . '_cache');

        // 📁 Storage Configuration - Stelle sicher dass Verzeichnisse existieren
        $this->ensureTenantStorageDirectories($tenant->id);
    }

    /**
     * Erstelle tenant-spezifische Storage-Verzeichnisse wenn sie nicht existieren
     */
    protected function ensureTenantStorageDirectories(string $tenantId): void
    {
        // Basis Storage-Pfad (noch NICHT von Tenancy überschrieben)
        $baseStoragePath = base_path('storage');

        // Tenant Storage-Pfad
        $tenantStoragePath = $baseStoragePath . DIRECTORY_SEPARATOR . 'tenant' . $tenantId;

        $directories = [
            // Framework Cache/Sessions/Views
            $tenantStoragePath . DIRECTORY_SEPARATOR . 'framework',
            $tenantStoragePath . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache',
            $tenantStoragePath . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'data',
            $tenantStoragePath . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions',
            $tenantStoragePath . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views',

            // Logs
            $tenantStoragePath . DIRECTORY_SEPARATOR . 'logs',

            // App directories
            $tenantStoragePath . DIRECTORY_SEPARATOR . 'app',
            $tenantStoragePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public',

            // Public uploads (im zentralen storage/app/public für Symlink)
            $baseStoragePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'tenant_' . $tenantId,
            $baseStoragePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'tenant_' . $tenantId . DIRECTORY_SEPARATOR . 'club-logos',
            $baseStoragePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'tenant_' . $tenantId . DIRECTORY_SEPARATOR . 'club-favicons',
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                @mkdir($directory, 0755, true);
            }
        }
    }

    /**
     * Get tenant settings from database
     */
    protected function getTenantSettings(): ?array
    {
        try {
            // Versuche Settings aus Tenant-DB zu laden
            if (class_exists(\App\Models\Tenant\Setting::class)) {
                $settings = \App\Models\Tenant\Setting::pluck('payload', 'name')->toArray();

                // JSON decode values
                return array_map(function ($value) {
                    $decoded = json_decode($value, true);
                    return $decoded ?? $value;
                }, $settings);
            }

            return null;
        } catch (\Exception $e) {
            // Fallback wenn Settings Tabelle noch nicht existiert
            \Log::debug('Could not load tenant settings: ' . $e->getMessage());
            return null;
        }
    }
}
