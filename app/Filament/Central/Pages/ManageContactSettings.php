<?php

namespace App\Filament\Central\Pages;

use App\Settings\ContactSettings;
use BackedEnum;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

class ManageContactSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string $settings = ContactSettings::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Kontakt & Adresse';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Einstellungen';
    }

    public function getTitle(): string
    {
        return 'Kontakt & Adresse Einstellungen';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Firmeninformationen')
                    ->description('Grundlegende Kontaktinformationen')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Firmenname')
                            ->placeholder('Klubportal GmbH')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Adresse')
                    ->description('Vollständige Adressinformationen')
                    ->schema([
                        Forms\Components\TextInput::make('street')
                            ->label('Straße & Hausnummer')
                            ->placeholder('Musterstraße 123')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('postal_code')
                                    ->label('PLZ')
                                    ->placeholder('12345'),

                                Forms\Components\TextInput::make('city')
                                    ->label('Stadt')
                                    ->placeholder('München'),
                            ]),

                        Forms\Components\TextInput::make('country')
                            ->label('Land')
                            ->placeholder('Deutschland')
                            ->default('Deutschland')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Kontaktdaten')
                    ->description('Telefon und E-Mail')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->prefixIcon('heroicon-o-phone')
                            ->placeholder('+49 123 456789'),

                        Forms\Components\TextInput::make('mobile')
                            ->label('Mobil')
                            ->tel()
                            ->prefixIcon('heroicon-o-device-phone-mobile')
                            ->placeholder('+49 160 123456'),

                        Forms\Components\TextInput::make('fax')
                            ->label('Fax')
                            ->tel()
                            ->prefixIcon('heroicon-o-printer')
                            ->placeholder('+49 123 456788'),

                        Forms\Components\TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->prefixIcon('heroicon-o-envelope')
                            ->placeholder('info@klubportal.com'),
                    ])
                    ->columns(2),

                Section::make('Google Maps')
                    ->description('Google Maps Integration')
                    ->schema([
                        Forms\Components\TextInput::make('google_maps_url')
                            ->label('Google Maps URL')
                            ->url()
                            ->prefixIcon('heroicon-o-map')
                            ->placeholder('https://maps.google.com/?q=...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('google_maps_embed')
                            ->label('Google Maps Embed Code')
                            ->placeholder('<iframe src="https://www.google.com/maps/embed?..." ...</iframe>')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}
