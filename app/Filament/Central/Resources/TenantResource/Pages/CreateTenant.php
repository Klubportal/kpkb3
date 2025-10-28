<?php

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Filament\Central\Resources\TenantResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hash das Admin-Passwort bevor es in data gespeichert wird
        if (isset($data['data']['admin_password'])) {
            $data['data']['admin_password'] = bcrypt($data['data']['admin_password']);
        }

        // Setze Default Plan
        if (!isset($data['plan_id'])) {
            $data['plan'] = 'free';
        }

        // Füge Metadaten hinzu
        $data['data']['registered_at'] = now()->toIso8601String();
        $data['data']['registered_via'] = 'filament_admin';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Erstelle die Domain
        $tenant = $this->record;
        $tenant->domains()->create([
            'domain' => $tenant->id . '.localhost'
        ]);

        // Info-Benachrichtigung
        Notification::make()
            ->success()
            ->title('Tenant erfolgreich erstellt')
            ->body("COMET-Daten werden automatisch im Hintergrund synchronisiert.")
            ->persistent()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
