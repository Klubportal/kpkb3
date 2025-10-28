<?php

namespace App\Filament\Club\Resources\SyncLogs\Pages;

use App\Filament\Club\Resources\SyncLogs\SyncLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSyncLog extends EditRecord
{
    protected static string $resource = SyncLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
