<?php

namespace App\Filament\Club\Resources\CometPlayers\Pages;

use App\Filament\Club\Resources\CometPlayers\CometPlayerResource;
use App\Models\ClubPlayer;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCometPlayer extends EditRecord
{
    protected static string $resource = CometPlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load club-specific data if it exists
        $clubPlayer = $this->record->clubPlayer;

        if ($clubPlayer) {
            $data = array_merge($data, $clubPlayer->toArray());
        }

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Fields that belong to club_players table
        $clubPlayerFields = [
            'email',
            'phone',
            'mobile',
            'address',
            'postal_code',
            'city',
            'parent1_name',
            'parent1_email',
            'parent1_phone',
            'parent1_mobile',
            'parent2_name',
            'parent2_email',
            'parent2_phone',
            'parent2_mobile',
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_relation',
            'has_medical_clearance',
            'medical_clearance_date',
            'medical_notes',
            'allergies',
            'notes',
        ];

        // Extract club-specific data
        $clubData = [];
        foreach ($clubPlayerFields as $field) {
            if (isset($data[$field])) {
                $clubData[$field] = $data[$field];
                unset($data[$field]); // Remove from comet_players data
            }
        }

        // Update or create club_players record
        if (!empty($clubData)) {
            // Add basic info from comet_players for easier querying
            $clubData['comet_player_id'] = $record->id;
            $clubData['first_name'] = $data['first_name'] ?? $record->first_name;
            $clubData['last_name'] = $data['last_name'] ?? $record->last_name;
            $clubData['popular_name'] = $data['popular_name'] ?? $record->popular_name;
            $clubData['date_of_birth'] = $data['date_of_birth'] ?? $record->date_of_birth;
            $clubData['nationality'] = $data['nationality'] ?? $record->nationality;
            $clubData['position'] = $data['position'] ?? $record->position;
            $clubData['jersey_number'] = $data['shirt_number'] ?? $record->shirt_number;
            $clubData['photo_url'] = $data['photo_url'] ?? $record->photo_url;
            $clubData['active'] = ($data['status'] ?? $record->status) === 'active';

            ClubPlayer::updateOrCreate(
                ['comet_player_id' => $record->id],
                $clubData
            );
        }

        // Update comet_players with remaining data
        $record->update($data);

        return $record;
    }
}
