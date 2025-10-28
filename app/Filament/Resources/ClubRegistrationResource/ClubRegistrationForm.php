<?php

namespace App\Filament\Resources\ClubRegistrationResource;

use Filament\Schemas\Schema;
use Filament\Forms;

class ClubRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('club_name')->required()->maxLength(100),
            Forms\Components\TextInput::make('subdomain')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('contact_person')->required()->maxLength(100),
            Forms\Components\TextInput::make('phone')->maxLength(20),
            Forms\Components\Select::make('template')
                ->options([
                    'kb' => 'KB Template',
                    'bm' => 'BM Template',
                ])->required(),
            Forms\Components\Textarea::make('admin_notes'),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Ausstehend',
                    'approved' => 'Freigeschaltet',
                    'rejected' => 'Abgelehnt',
                ])->required(),
            Forms\Components\DateTimePicker::make('approved_at'),
            Forms\Components\TextInput::make('approved_by')->numeric(),
        ]);
    }
}
