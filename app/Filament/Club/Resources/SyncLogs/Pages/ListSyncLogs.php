<?php

namespace App\Filament\Club\Resources\SyncLogs\Pages;

use App\Filament\Club\Resources\SyncLogs\SyncLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSyncLogs extends ListRecords
{
    protected static string $resource = SyncLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
