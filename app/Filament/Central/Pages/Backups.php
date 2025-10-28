<?php

namespace App\Filament\Central\Pages;

// Provide a safe fallback if the Backup plugin is not installed in this environment
if (class_exists(\ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups::class)) {
    class Backups extends \ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups
    {
        public static function getNavigationGroup(): ?string
        {
            return __('Backups');
        }

        public static function getNavigationLabel(): string
        {
            return __('Sicherungen');
        }

        public static function getNavigationSort(): ?int
        {
            return 1;
        }
    }
} else {
    class Backups
    {
        // Plugin not available; fallback stub so composer discovery doesn't fail.
    }
}
