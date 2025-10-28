<?php

namespace App\Filament\Club\Resources\CometPlayers\Pages;

use App\Filament\Club\Resources\CometPlayers\CometPlayerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCometPlayer extends ViewRecord
{
    protected static string $resource = CometPlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
