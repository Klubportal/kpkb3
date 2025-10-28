<?php

namespace App\Filament\Club\Resources\CometPlayers\Pages;

use App\Filament\Club\Resources\CometPlayers\CometPlayerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCometPlayers extends ListRecords
{
    protected static string $resource = CometPlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()->with('playerGroups');
    }
}
