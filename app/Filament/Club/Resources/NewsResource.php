<?php

namespace App\Filament\Club\Resources;

use App\Filament\Club\Resources\NewsResource\Pages;
use App\Models\News;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'News';

    protected static ?string $modelLabel = 'News';

    protected static ?string $pluralModelLabel = 'News';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Grundinformationen')
                ->schema([
                    TextInput::make('title')
                        ->label('Titel')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state)))
                        ->columnSpanFull(),

                    TextInput::make('slug')
                        ->label('URL Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    Textarea::make('excerpt')
                        ->label('Kurzbeschreibung')
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Inhalt')
                ->schema([
                    RichEditor::make('content')
                        ->label('Inhalt')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'link',
                            'h2',
                            'h3',
                            'bulletList',
                            'orderedList',
                            'blockquote',
                            'undo',
                            'redo',
                        ])
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Medien')
                ->schema([
                    FileUpload::make('featured_image')
                        ->label('Hauptbild')
                        ->image()
                        ->disk('public')
                        ->directory('news')
                        ->visibility('public')
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '16:9',
                            '4:3',
                            '1:1',
                        ])
                        ->maxSize(5120)
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Einstellungen')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Entwurf',
                            'published' => 'Veröffentlicht',
                            'archived' => 'Archiviert',
                        ])
                        ->default('draft')
                        ->required(),

                    DateTimePicker::make('published_at')
                        ->label('Veröffentlichungsdatum')
                        ->default(now())
                        ->required(),

                    Toggle::make('is_featured')
                        ->label('Als Featured markieren')
                        ->default(false),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            Tables\Columns\TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Veröffentlicht')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

            Tables\Columns\TextColumn::make('views_count')
                    ->label('Aufrufe')
                    ->numeric()
                    ->sortable(),
        ];

        // Add tags column using plugin if available; else use core TagsColumn
        if (class_exists('Filament\\Tables\\Columns\\SpatieTagsColumn')) {
            $columns[] = (\Filament\Tables\Columns\SpatieTagsColumn::make('tags'))
                ->label('Tags')
                ->type('news');
        } else {
            $columns[] = (\Filament\Tables\Columns\TagsColumn::make('tags'))
                ->label('Tags');
        }

        return $table
            ->columns($columns)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Entwurf',
                        'published' => 'Veröffentlicht',
                        'archived' => 'Archiviert',
                    ]),
            ])
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]))
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
