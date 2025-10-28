<?php

namespace App\Filament\Central\Pages;

use Filament\Pages\Page;

class RestoreBackup extends Page
{
    protected string $view = 'filament.central.pages.restore-backup';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-uturn-left';
    }

    public static function getNavigationLabel(): string
    {
        return __('Wiederherstellen');
    }

    public function getTitle(): string
    {
        return __('Restore Backup');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Backups');
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}
