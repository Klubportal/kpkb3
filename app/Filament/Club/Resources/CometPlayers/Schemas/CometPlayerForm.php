<?php

namespace App\Filament\Club\Resources\CometPlayers\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class CometPlayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Spieler Details')
                    ->tabs([
                        Tabs\Tab::make('Grunddaten')
                            ->schema([
                                Section::make('Persönliche Daten')
                                    ->schema([
                                        FileUpload::make('photo_url')
                                            ->label('Foto')
                                            ->image()
                                            ->imageEditor()
                                            ->directory('player-photos')
                                            ->columnSpanFull(),
                                        TextInput::make('first_name')
                                            ->label('Vorname')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->label('Nachname')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('popular_name')
                                            ->label('Spitzname')
                                            ->maxLength(255),
                                        DatePicker::make('date_of_birth')
                                            ->label('Geburtsdatum')
                                            ->native(false)
                                            ->displayFormat('d.m.Y')
                                            ->required(),
                                        TextInput::make('birth_year')
                                            ->label('Jahrgang')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false),
                                        TextInput::make('place_of_birth')
                                            ->label('Geburtsort')
                                            ->maxLength(255),
                                        Select::make('gender')
                                            ->label('Geschlecht')
                                            ->options([
                                                'male' => 'Männlich',
                                                'female' => 'Weiblich',
                                            ]),
                                        TextInput::make('nationality')
                                            ->label('Nationalität')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Section::make('Spieler Information')
                                    ->schema([
                                        TextInput::make('shirt_number')
                                            ->label('Rückennummer')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(99),
                                        Select::make('position')
                                            ->label('Position')
                                            ->options([
                                                'goalkeeper' => 'Torwart',
                                                'defender' => 'Verteidiger',
                                                'midfielder' => 'Mittelfeld',
                                                'forward' => 'Stürmer',
                                            ])
                                            ->required(),
                                        TextInput::make('primary_age_category')
                                            ->label('Alterskategorie')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Select::make('foot')
                                            ->label('Starker Fuß')
                                            ->options([
                                                'left' => 'Links',
                                                'right' => 'Rechts',
                                                'both' => 'Beide',
                                            ]),
                                        TextInput::make('height_cm')
                                            ->label('Größe (cm)')
                                            ->numeric()
                                            ->suffix('cm'),
                                        TextInput::make('weight_kg')
                                            ->label('Gewicht (kg)')
                                            ->numeric()
                                            ->suffix('kg'),
                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'active' => 'Aktiv',
                                                'injured' => 'Verletzt',
                                                'suspended' => 'Gesperrt',
                                                'inactive' => 'Inaktiv',
                                            ])
                                            ->required()
                                            ->default('active'),
                                    ])
                                    ->columns(3),

                                Section::make('Team / Gruppen Zuordnung')
                                    ->schema([
                                        \Filament\Forms\Components\CheckboxList::make('playerGroups')
                                            ->label('Teams/Gruppen')
                                            ->relationship('playerGroups', 'name')
                                            ->options(fn () => \App\Models\Group::where('active', true)->pluck('name', 'id'))
                                            ->columns(2)
                                            ->searchable()
                                            ->bulkToggleable()
                                            ->helperText('Wählen Sie die Teams/Gruppen aus, denen dieser Spieler zugeordnet ist.'),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tabs\Tab::make('Kontaktdaten')
                            ->schema([
                                Section::make('Spieler Kontakt')
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('E-Mail')
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->label('Telefon')
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('mobile')
                                            ->label('Handy')
                                            ->tel()
                                            ->maxLength(255),
                                        Textarea::make('address')
                                            ->label('Adresse')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        TextInput::make('postal_code')
                                            ->label('PLZ')
                                            ->maxLength(10),
                                        TextInput::make('city')
                                            ->label('Stadt')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Section::make('Eltern / Erziehungsberechtigte 1')
                                    ->schema([
                                        TextInput::make('parent1_name')
                                            ->label('Name')
                                            ->maxLength(255),
                                        TextInput::make('parent1_email')
                                            ->label('E-Mail')
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('parent1_phone')
                                            ->label('Telefon')
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('parent1_mobile')
                                            ->label('Handy')
                                            ->tel()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Section::make('Eltern / Erziehungsberechtigte 2')
                                    ->schema([
                                        TextInput::make('parent2_name')
                                            ->label('Name')
                                            ->maxLength(255),
                                        TextInput::make('parent2_email')
                                            ->label('E-Mail')
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('parent2_phone')
                                            ->label('Telefon')
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('parent2_mobile')
                                            ->label('Handy')
                                            ->tel()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Section::make('Notfallkontakt')
                                    ->schema([
                                        TextInput::make('emergency_contact_name')
                                            ->label('Name')
                                            ->maxLength(255),
                                        TextInput::make('emergency_contact_phone')
                                            ->label('Telefon')
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('emergency_contact_relation')
                                            ->label('Beziehung')
                                            ->maxLength(255)
                                            ->placeholder('z.B. Oma, Onkel, etc.'),
                                    ])
                                    ->columns(3),
                            ]),

                        Tabs\Tab::make('Medizinisch')
                            ->schema([
                                Section::make('Medizinische Freigabe')
                                    ->schema([
                                        Toggle::make('has_medical_clearance')
                                            ->label('Medizinische Freigabe vorhanden')
                                            ->inline(false),
                                        DatePicker::make('medical_clearance_date')
                                            ->label('Datum der Freigabe')
                                            ->native(false)
                                            ->displayFormat('d.m.Y'),
                                        Textarea::make('medical_notes')
                                            ->label('Medizinische Hinweise')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('allergies')
                                            ->label('Allergien')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Statistiken')
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Section::make('Saison Statistiken')
                                            ->schema([
                                                TextInput::make('season_matches')
                                                    ->label('Spiele')
                                                    ->numeric(),
                                                TextInput::make('season_minutes_played')
                                                    ->label('Minuten')
                                                    ->numeric()
                                                    ->suffix('min'),
                                                TextInput::make('season_goals')
                                                    ->label('Tore')
                                                    ->numeric(),
                                                TextInput::make('season_assists')
                                                    ->label('Vorlagen')
                                                    ->numeric(),
                                                TextInput::make('season_yellow_cards')
                                                    ->label('Gelbe Karten')
                                                    ->numeric(),
                                                TextInput::make('season_red_cards')
                                                    ->label('Rote Karten')
                                                    ->numeric(),
                                            ])
                                            ->columns(3),

                                        Section::make('Gesamt Statistiken')
                                            ->schema([
                                                TextInput::make('total_matches')
                                                    ->label('Spiele')
                                                    ->numeric()
                                                    ->disabled(),
                                                TextInput::make('total_minutes_played')
                                                    ->label('Minuten')
                                                    ->numeric()
                                                    ->suffix('min')
                                                    ->disabled(),
                                                TextInput::make('total_goals')
                                                    ->label('Tore')
                                                    ->numeric()
                                                    ->disabled(),
                                                TextInput::make('total_assists')
                                                    ->label('Vorlagen')
                                                    ->numeric()
                                                    ->disabled(),
                                                TextInput::make('total_yellow_cards')
                                                    ->label('Gelbe Karten')
                                                    ->numeric()
                                                    ->disabled(),
                                                TextInput::make('total_red_cards')
                                                    ->label('Rote Karten')
                                                    ->numeric()
                                                    ->disabled(),
                                            ])
                                            ->columns(3),
                                    ]),
                            ]),

                        Tabs\Tab::make('Notizen')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Allgemeine Notizen')
                                    ->rows(10)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
