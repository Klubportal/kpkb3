<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\TemplateResource\Pages;
use App\Models\Central\Template;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Template Name'),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->label('Slug'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->label('Beschreibung'),

                        Forms\Components\FileUpload::make('preview_image')
                            ->image()
                            ->directory('templates')
                            ->label('Vorschau-Bild'),
                    ])->columns(2),

                Forms\Components\Section::make('Features & Farben')
                    ->schema([
                        Forms\Components\TagsInput::make('features')
                            ->placeholder('Feature hinzufügen...')
                            ->label('Features'),

                        Forms\Components\KeyValue::make('colors')
                            ->keyLabel('Farbe')
                            ->valueLabel('Hex-Wert')
                            ->addActionLabel('Farbe hinzufügen')
                            ->label('Farbschema'),
                    ])->columns(1),

                Forms\Components\Section::make('Einstellungen')
                    ->schema([
                        Forms\Components\TextInput::make('layout_path')
                            ->default('layouts.frontend')
                            ->required()
                            ->label('Layout Pfad'),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sortierung'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Aktiv'),

                        Forms\Components\Toggle::make('is_default')
                            ->default(false)
                            ->label('Standard-Template'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview_image')
                    ->label('Vorschau')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->badge()
                    ->label('Slug'),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Standard'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktiv'),

                Tables\Columns\TextColumn::make('tenants_count')
                    ->counts('tenants')
                    ->badge()
                    ->color('success')
                    ->label('Verwendungen'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->label('Reihenfolge'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Nur aktive'),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Nur Standard'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }
}
