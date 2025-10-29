<?php

namespace App\Filament\Club\Resources;

use App\Filament\Club\Resources\TemplateSettingResource\Pages;
use App\Models\Tenant\TemplateSetting;
use App\Models\Central\Template;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Support\Icons\Heroicon;

class TemplateSettingResource extends Resource
{
    protected static ?string $model = TemplateSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?string $navigationLabel = 'Website Einstellungen';

    protected static ?string $modelLabel = 'Website Einstellung';

    protected static ?string $pluralModelLabel = 'Website Einstellungen';

    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Grundeinstellungen')
                ->schema([
                    Select::make('template_id')
                        ->label('Website-Template')
                        ->options(function () {
                            return Template::where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('name', 'id');
                        })
                        ->afterStateUpdated(function ($state) {
                            if (tenant()) {
                                $tenant = tenant();
                                $tenant->template_id = $state;
                                $tenant->save();
                            }
                        })
                        ->default(function () {
                            if (tenant()) {
                                $tenant = tenant();
                                return $tenant->template_id ?? Template::where('is_default', true)->first()?->id;
                            }
                            return null;
                        })
                        ->helperText('Wählen Sie das Design-Template für Ihre Website'),

                    TextInput::make('website_name')
                        ->label('Website Name')
                        ->default('Mein Verein')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('slogan')
                        ->label('Slogan')
                        ->maxLength(255),

                    TextInput::make('club_fifa_id')
                        ->label('Club FIFA ID')
                        ->numeric()
                        ->required()
                        ->helperText('Die FIFA ID des Vereins (z.B. 396 für NK Naprijed)'),
                ])
                ->columns(2),

            Section::make('Farben')
                ->schema([
                    ColorPicker::make('primary_color')
                        ->label('Primärfarbe')
                        ->default('#DC052D'),
                    ColorPicker::make('secondary_color')
                        ->label('Sekundärfarbe')
                        ->default('#0066B2'),
                    ColorPicker::make('accent_color')
                        ->label('Akzentfarbe')
                        ->default('#FCBF49'),
                    ColorPicker::make('text_color')
                        ->label('Textfarbe')
                        ->default('#1f2937'),
                    ColorPicker::make('badge_bg_color')
                        ->label('Badge Hintergrund'),
                    ColorPicker::make('badge_text_color')
                        ->label('Badge Text'),
                    ColorPicker::make('hero_bg_color')
                        ->label('Hero Hintergrund'),
                    ColorPicker::make('hero_text_color')
                        ->label('Hero Text'),
                ])
                ->columns(4),

            Section::make('Header')
                ->schema([
                    ColorPicker::make('header_bg_color')
                        ->label('Header Hintergrundfarbe')
                        ->default('#FFFFFF'),
                    ColorPicker::make('header_text_color')
                        ->label('Header Textfarbe')
                        ->default('#1F2937'),
                    Select::make('header_style')
                        ->label('Header Style')
                        ->options([
                            'default' => 'Standard',
                            'modern' => 'Modern',
                            'classic' => 'Klassisch',
                        ])
                        ->default('default'),
                    Toggle::make('sticky_header')
                        ->label('Sticky Header')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Logo')
                ->schema([
                    FileUpload::make('logo')
                        ->label('Logo hochladen')
                        ->image()
                        ->disk('public')
                        ->directory('logos')
                        ->visibility('public')
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            null,
                            '16:9',
                            '4:3',
                            '1:1',
                        ])
                        ->maxSize(2048)
                        ->helperText('Empfohlen: PNG oder SVG, max. 2 MB'),
                    TextInput::make('logo_height')
                        ->label('Logo Höhe (px)')
                        ->numeric()
                        ->default(50)
                        ->minValue(20)
                        ->maxValue(200),
                    Toggle::make('show_logo')
                        ->label('Logo anzeigen')
                        ->default(true),
                ])
                ->columns(3),

            Section::make('Footer')
                ->schema([
                    Textarea::make('footer_about')
                        ->label('Über uns Text')
                        ->rows(3),
                    TextInput::make('footer_email')
                        ->label('E-Mail')
                        ->email(),
                    TextInput::make('footer_phone')
                        ->label('Telefon'),
                    TextInput::make('footer_address')
                        ->label('Adresse'),
                    ColorPicker::make('footer_bg_color')
                        ->label('Hintergrundfarbe'),
                    ColorPicker::make('footer_text_color')
                        ->label('Textfarbe'),
                ])
                ->columns(2),

            Section::make('Social Media')
                ->schema([
                    TextInput::make('facebook_url')
                        ->label('Facebook URL')
                        ->url()
                        ->placeholder('https://facebook.com/...'),
                    TextInput::make('twitter_url')
                        ->label('Twitter URL')
                        ->url()
                        ->placeholder('https://twitter.com/...'),
                    TextInput::make('instagram_url')
                        ->label('Instagram URL')
                        ->url()
                        ->placeholder('https://instagram.com/...'),
                    TextInput::make('youtube_url')
                        ->label('YouTube URL')
                        ->url()
                        ->placeholder('https://youtube.com/...'),
                    TextInput::make('tiktok_url')
                        ->label('TikTok URL')
                        ->url()
                        ->placeholder('https://tiktok.com/...'),
                ])
                ->columns(2),

            Section::make('Startseite Widgets')
                ->schema([
                    Toggle::make('show_news')
                        ->label('News anzeigen')
                        ->default(true),
                    TextInput::make('news_count')
                        ->label('Anzahl News')
                        ->numeric()
                        ->default(3)
                        ->minValue(1)
                        ->maxValue(10),
                    Toggle::make('show_next_match')
                        ->label('Nächstes Spiel anzeigen')
                        ->default(true),
                    Toggle::make('show_last_results')
                        ->label('Letzte Ergebnisse anzeigen')
                        ->default(true),
                    Toggle::make('show_standings')
                        ->label('Tabelle anzeigen')
                        ->default(true),
                    Toggle::make('show_top_scorers')
                        ->label('Top Torschützen anzeigen')
                        ->default(true),
                ])
                ->columns(3),

            Section::make('Erweitert')
                ->schema([
                    Toggle::make('enable_dark_mode')
                        ->label('Dark Mode aktivieren')
                        ->default(false),
                    Toggle::make('enable_animations')
                        ->label('Animationen aktivieren')
                        ->default(true),
                    TextInput::make('google_analytics_id')
                        ->label('Google Analytics ID')
                        ->placeholder('G-XXXXXXXXXX'),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('website_name')
                    ->label('Website Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slogan')
                    ->label('Slogan')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\ColorColumn::make('primary_color')
                    ->label('Primärfarbe'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplateSettings::route('/'),
            'edit' => Pages\EditTemplateSetting::route('/{record}/edit'),
        ];
    }
}
