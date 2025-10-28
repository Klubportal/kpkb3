<?php

namespace App\Filament\Central\Resources\ClubResource\Pages;

use App\Filament\Central\Resources\ClubResource;
use Filament\Resources\Pages\ListRecords;

class ListClubs extends ListRecords
{
    protected static string $resource = ClubResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
