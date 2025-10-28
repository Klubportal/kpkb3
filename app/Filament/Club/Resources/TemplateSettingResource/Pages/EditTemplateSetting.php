<?php

namespace App\Filament\Club\Resources\TemplateSettingResource\Pages;

use App\Filament\Club\Resources\TemplateSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditTemplateSetting extends EditRecord
{
    protected static string $resource = TemplateSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Vorschau')
                ->icon('heroicon-o-eye')
                ->url(fn () => url('/'), shouldOpenInNewTab: true)
                ->color('info'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Einstellungen gespeichert')
            ->body('Die Website-Einstellungen wurden erfolgreich aktualisiert.');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Lade template_id aus der tenants Tabelle
        if (tenant()) {
            $tenant = tenant();
            $data['template_id'] = $tenant->template_id;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Speichere template_id in der tenants Tabelle
        if (isset($data['template_id']) && tenant()) {
            $tenant = tenant();
            $tenant->template_id = $data['template_id'];
            $tenant->save();

            // Entferne template_id aus data, da es nicht in template_settings Tabelle gespeichert wird
            unset($data['template_id']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Bleibe auf der Bearbeitungsseite nach dem Speichern
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
