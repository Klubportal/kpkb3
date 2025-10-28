<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\PageResource\Pages\ManagePages;
use App\Models\Central\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Content');
    }

    public static function getNavigationLabel(): string
    {
        return __('Pages');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title.de')
                    ->label('Titel (DE)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('title.en')
                    ->label('Title (EN)')
                    ->maxLength(255),

                Textarea::make('excerpt.de')
                    ->label('Auszug (DE)')
                    ->rows(3)
                    ->maxLength(500),
                Textarea::make('excerpt.en')
                    ->label('Excerpt (EN)')
                    ->rows(3)
                    ->maxLength(500),

                MarkdownEditor::make('content.de')
                    ->label('Inhalt (DE)')
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'heading',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'table',
                        'undo',
                    ]),

                MarkdownEditor::make('content.en')
                    ->label('Content (EN)')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'heading',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'table',
                        'undo',
                    ]),

                Select::make('status')
                    ->options([
                        'draft' => 'Entwurf',
                        'published' => 'Veröffentlicht',
                        'archived' => 'Archiviert',
                    ])
                    ->default('draft')
                    ->required(),

                DateTimePicker::make('published_at')
                    ->label('Veröffentlichungsdatum'),

                Select::make('template')
                    ->label('Seitenvorlage')
                    ->options([
                        'default' => 'Standard',
                        'landing' => 'Landing Page',
                        'contact' => 'Kontakt',
                        'about' => 'Über uns',
                    ])
                    ->default('default'),

                SpatieMediaLibraryFileUpload::make('featured_image')
                    ->label('Hauptbild')
                    ->collection('featured')
                    ->image()
                    ->imageEditor()
                    ->maxSize(5120),

                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Galerie')
                    ->collection('gallery')
                    ->multiple()
                    ->image()
                    ->imageEditor()
                    ->maxSize(5120)
                    ->columnSpanFull(),

                SpatieTagsInput::make('tags')
                    ->label('Tags')
                    ->helperText('Drücken Sie Enter nach jedem Tag um ihn hinzuzufügen.')
                    ->placeholder('Neuer Tag...')
                    ->columnSpanFull(),

                TextInput::make('seo_title.de')
                    ->label('SEO Titel (DE)')
                    ->maxLength(60),
                TextInput::make('seo_title.en')
                    ->label('SEO Title (EN)')
                    ->maxLength(60),

                Textarea::make('seo_description.de')
                    ->label('SEO Beschreibung (DE)')
                    ->rows(2)
                    ->maxLength(160),
                Textarea::make('seo_description.en')
                    ->label('SEO Description (EN)')
                    ->rows(2)
                    ->maxLength(160),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('template'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePages::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
