<?php

namespace App\Filament\Central\Resources\NewsResource\Pages;

use App\Filament\Central\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNews extends ManageRecords
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Auto-populate author_id with current user
                    $data['author_id'] = auth()->id();

                    // Log für Debugging
                    \Log::info('Creating News', $data);
                    return $data;
                }),
        ];
    }
}
