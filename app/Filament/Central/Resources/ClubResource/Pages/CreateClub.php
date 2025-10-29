<?php

namespace App\Filament\Central\Resources\ClubResource\Pages;

use App\Filament\Central\Resources\ClubResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClub extends CreateRecord
{
    protected static string $resource = ClubResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
