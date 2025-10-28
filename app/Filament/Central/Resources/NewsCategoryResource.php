<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\NewsCategoryResource\Pages;
use App\Models\Central\NewsCategory;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;

    public static function getNavigationLabel(): string
    {
        return 'News Categories';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Multi-Language Name (Backend: nur DE + EN)
                TextInput::make('name.de')
                    ->label('🇩🇪 Name (Deutsch)')
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, callable $set) {
                        if ($operation === 'create' && $state) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('name.en')
                    ->label('🇬🇧 Name (English)')
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, callable $set) {
                        if ($operation === 'create' && $state) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(NewsCategory::class, 'slug', ignoreRecord: true)
                    ->helperText('Auto-generated from German name'),

                // Multi-Language Description (Backend: nur DE + EN)
                Textarea::make('description.de')
                    ->label('🇩🇪 Beschreibung (Deutsch)')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('description.en')
                    ->label('🇬🇧 Description (English)')
                    ->rows(2)
                    ->columnSpanFull(),

                TextInput::make('icon')
                    ->label('Icon Class')
                    ->helperText('e.g., heroicon-o-star')
                    ->maxLength(100),

                ColorPicker::make('color')
                    ->label('Category Color'),

                TextInput::make('order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->getTranslation('name', app()->getLocale())),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label('Color')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsCategories::route('/'),
            'create' => Pages\CreateNewsCategory::route('/create'),
            'edit' => Pages\EditNewsCategory::route('/{record}/edit'),
        ];
    }
}
