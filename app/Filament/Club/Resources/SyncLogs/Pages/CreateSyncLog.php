<?php

namespace App\Filament\Club\Resources\SyncLogs\Pages;

use App\Filament\Club\Resources\SyncLogs\SyncLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSyncLog extends CreateRecord
{
    protected static string $resource = SyncLogResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
