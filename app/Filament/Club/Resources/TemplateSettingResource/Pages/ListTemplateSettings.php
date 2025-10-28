<?php

namespace App\Filament\Club\Resources\TemplateSettingResource\Pages;

use App\Filament\Club\Resources\TemplateSettingResource;
use App\Models\Tenant\TemplateSetting;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemplateSettings extends ListRecords
{
    protected static string $resource = TemplateSettingResource::class;

    public function mount(): void
    {
        // Redirect directly to the edit page of the first (and only) settings record
        $setting = TemplateSetting::first();

        if ($setting) {
            $this->redirect(
                TemplateSettingResource::getUrl('edit', ['record' => $setting->id])
            );
            return;
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            // No create action - only one settings record
        ];
    }
}
