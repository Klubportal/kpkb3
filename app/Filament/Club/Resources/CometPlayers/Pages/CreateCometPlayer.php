<?php

namespace App\Filament\Club\Resources\CometPlayers\Pages;

use App\Filament\Club\Resources\CometPlayers\CometPlayerResource;
use App\Models\ClubPlayer;
use Filament\Resources\Pages\CreateRecord;

class CreateCometPlayer extends CreateRecord
{
    protected static string $resource = CometPlayerResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
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

        // Create comet_players record first
        $record = static::getModel()::create($data);

        // Create club_players record if we have club data
        if (!empty($clubData)) {
            $clubData['comet_player_id'] = $record->id;
            $clubData['first_name'] = $data['first_name'] ?? $record->first_name;
            $clubData['last_name'] = $data['last_name'] ?? $record->last_name;
            $clubData['popular_name'] = $data['popular_name'] ?? $record->popular_name;
            $clubData['date_of_birth'] = $data['date_of_birth'] ?? $record->date_of_birth;
            $clubData['nationality'] = $data['nationality'] ?? $record->nationality;
            $clubData['position'] = $data['position'] ?? $record->position;
            $clubData['jersey_number'] = $data['shirt_number'] ?? $record->shirt_number;
            $clubData['photo_url'] = $data['photo_url'] ?? $record->photo_url;
            $clubData['active'] = ($data['status'] ?? 'active') === 'active';

            ClubPlayer::create($clubData);
        }

        return $record;
    }
}
