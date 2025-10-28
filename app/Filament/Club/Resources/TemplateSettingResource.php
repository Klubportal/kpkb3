<?php

namespace App\Filament\Club\Resources;

use App\Filament\Club\Resources\TemplateSettingResource\Pages;
use App\Models\Tenant\TemplateSetting;
use App\Models\Central\Template;
use BackedEnum;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
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
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('Grundeinstellungen')
                            ->schema([
                                Select::make('template_id')
                                    ->label('Website-Template')
                                    ->options(function () {
                                        return Template::where('is_active', true)
                                            ->orderBy('sort_order')
                                            ->pluck('name', 'id');
                                    })
                                    ->afterStateUpdated(function ($state) {
                                        // Update tenant's template_id
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
                                    ->helperText('Wählen Sie das Design-Template für Ihre Website')
                                    ->columnSpanFull(),

                                TextInput::make('club_fifa_id')
                                    ->label('Club FIFA ID')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Die FIFA ID des Vereins (z.B. 396 für NK Naprijed)'),
                                Textarea::make('footer_about')
                                    ->label('Über uns (Footer)')
                                    ->rows(3),
                            ]),

                        Tabs\Tab::make('Farben')
                            ->schema([
                                ColorPicker::make('primary_color')
                                    ->label('Primärfarbe (Allgemein)')
                                    ->default('#DC052D'),
                                ColorPicker::make('secondary_color')
                                    ->label('Sekundärfarbe')
                                    ->default('#0066B2'),
                                ColorPicker::make('accent_color')
                                    ->label('Akzentfarbe')
                                    ->default('#FCBF49'),
                            ])
                            ->columns(3),

                        Tabs\Tab::make('Header Farben')
                            ->schema([
                                ColorPicker::make('header_bg_color')
                                    ->label('Header Hintergrundfarbe')
                                    ->default('#FFFFFF'),
                                ColorPicker::make('header_text_color')
                                    ->label('Header Textfarbe')
                                    ->default('#1F2937'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Hero Farben')
                            ->schema([
                                ColorPicker::make('hero_bg_color')
                                    ->label('Hero Hintergrundfarbe')
                                    ->default('#DC052D'),
                                ColorPicker::make('hero_text_color')
                                    ->label('Hero Textfarbe')
                                    ->default('#FFFFFF'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Badge & Icon Farben')
                            ->schema([
                                ColorPicker::make('badge_bg_color')
                                    ->label('Badge Hintergrundfarbe')
                                    ->default('#DC052D'),
                                ColorPicker::make('badge_text_color')
                                    ->label('Badge Textfarbe')
                                    ->default('#FFFFFF'),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Footer Farben')
                            ->schema([
                                ColorPicker::make('footer_bg_color')
                                    ->label('Footer Hintergrundfarbe')
                                    ->default('#1A1A1A'),
                                ColorPicker::make('footer_text_color')
                                    ->label('Footer Textfarbe')
                                    ->default('#FFFFFF'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('club_fifa_id')
                    ->label('FIFA ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('primary_color')
                    ->label('Primärfarbe'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                //
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
