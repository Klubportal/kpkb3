<?php

namespace App\Filament\Central\Resources\PageResource\Pages;

use App\Filament\Central\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePages extends ManageRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
