<?php

namespace App\Filament\Central\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ManageGeneralSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Site Einstellungen');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Einstellungen');
    }

    public function getTitle(): string
    {
        return __('Site Einstellungen');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Allgemeine Informationen'))
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label(__('Website Name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('site_description')
                            ->label(__('Beschreibung'))
                            ->rows(3)
                            ->maxLength(500),

                        Forms\Components\TextInput::make('contact_email')
                            ->label(__('Kontakt E-Mail'))
                            ->email()
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->label(__('Telefon'))
                            ->tel(),
                    ])
                    ->columns(2),

                Section::make(__('Branding'))
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label(__('Logo'))
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('favicon')
                            ->label(__('Favicon'))
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->acceptedFileTypes(['image/x-icon', 'image/png'])
                            ->columnSpan(1),

                        Forms\Components\Select::make('logo_height')
                            ->label(__('Logo Größe'))
                            ->options([
                                '2rem' => 'Klein (2rem)',
                                '2.5rem' => 'Mittel (2.5rem)',
                                '3rem' => 'Groß (3rem)',
                                '3.5rem' => 'Sehr Groß (3.5rem)',
                                '4rem' => 'Extra Groß (4rem)',
                                '5rem' => 'Riesig (5rem)',
                                '6rem' => 'Sehr Riesig (6rem)',
                                '7rem' => 'Extrem Groß (7rem)',
                                '8rem' => 'Maximal (8rem)',
                                '9rem' => 'Ultra (9rem)',
                                '10rem' => 'Gigantisch (10rem)',
                            ])
                            ->required()
                            ->helperText(__('Höhe des Logos im Header'))
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Section::make(__('Design'))
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label(__('Primärfarbe'))
                            ->required(),

                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label(__('Sekundärfarbe'))
                            ->required(),

                        Forms\Components\Select::make('font_family')
                            ->label(__('Schriftart'))
                            ->options([
                                'Inter' => 'Inter',
                                'Roboto' => 'Roboto',
                                'Open Sans' => 'Open Sans',
                                'Lato' => 'Lato',
                                'Montserrat' => 'Montserrat',
                                'Poppins' => 'Poppins',
                            ])
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('font_size')
                            ->label(__('Standard Schriftgröße (px)'))
                            ->numeric()
                            ->minValue(12)
                            ->maxValue(24)
                            ->suffix('px')
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }
}
