<?php

namespace App\Filament\Club\Resources\SyncLogs\Pages;

use App\Filament\Club\Resources\SyncLogs\SyncLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSyncLog extends ViewRecord
{
    protected static string $resource = SyncLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
