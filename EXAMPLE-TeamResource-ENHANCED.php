<?php

namespace App\Filament\SuperAdmin\Resources\Teams;

use App\Filament\SuperAdmin\Resources\Teams\Pages\ManageTeams;
use App\Models\Team;
use App\Services\AIService;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Vereinsverwaltung';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Basis-Informationen')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Mannschaftsname')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                        $set('slug', Str::slug($state))
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->label('URL-Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Select::make('season_id')
                                    ->label('Saison')
                                    ->relationship('season', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->label('Saison (z.B. 2024/2025)'),
                                        Forms\Components\DatePicker::make('start_date')
                                            ->label('Start'),
                                        Forms\Components\DatePicker::make('end_date')
                                            ->label('Ende'),
                                    ]),

                                Forms\Components\Select::make('category')
                                    ->label('Kategorie')
                                    ->options([
                                        'senior' => 'Senioren',
                                        'u19' => 'U19',
                                        'u17' => 'U17',
                                        'u15' => 'U15',
                                        'u13' => 'U13',
                                        'u11' => 'U11',
                                    ])
                                    ->searchable(),

                                Forms\Components\Select::make('league')
                                    ->label('Liga')
                                    ->options([
                                        'bundesliga' => 'Bundesliga',
                                        'regionalliga' => 'Regionalliga',
                                        'landesliga' => 'Landesliga',
                                        'bezirksliga' => 'Bezirksliga',
                                    ])
                                    ->searchable(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktiv')
                                    ->default(true),
                            ]),
                    ]),

                Forms\Components\Section::make('Team-Foto & Logo')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('team_photo')
                                    ->label('Mannschaftsfoto')
                                    ->collection('team_photos')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(5120)
                                    ->helperText('Max. 5 MB'),

                                SpatieMediaLibraryFileUpload::make('logo')
                                    ->label('Team-Logo')
                                    ->collection('team_logos')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(2048)
                                    ->helperText('Max. 2 MB'),
                            ]),
                    ]),

                Forms\Components\Section::make('Beschreibung')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Teambeschreibung')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->suffixAction(
                                Action::make('generate_with_ai')
                                    ->label('Mit KI generieren')
                                    ->icon('heroicon-o-sparkles')
                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                        try {
                                            $ai = new AIService();
                                            $description = $ai->generateWithOpenAI(
                                                "Erstelle eine professionelle Teambeschreibung für: {$get('name')}, Kategorie: {$get('category')}"
                                            );
                                            $set('description', $description);

                                            Notification::make()
                                                ->title('Beschreibung generiert')
                                                ->success()
                                                ->send();
                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->title('Fehler')
                                                ->body('KI-Service nicht verfügbar. Bitte API Key in .env eintragen.')
                                                ->danger()
                                                ->send();
                                        }
                                    })
                            ),
                    ]),

                Forms\Components\Section::make('Tags & Kategorisierung')
                    ->schema([
                        SpatieTagsInput::make('tags')
                            ->label('Tags')
                            ->helperText('z.B. Jugend, Leistungssport, Breitensport'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label('Logo')
                    ->collection('team_logos')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-team.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user-group'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategorie')
                    ->badge()
                    ->colors([
                        'success' => 'senior',
                        'warning' => fn ($state) => str_starts_with($state ?? '', 'u'),
                    ])
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? 'N/A')),

                Tables\Columns\TextColumn::make('league')
                    ->label('Liga')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('players_count')
                    ->label('Spieler')
                    ->counts('players')
                    ->suffix(' Spieler')
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategorie')
                    ->options([
                        'senior' => 'Senioren',
                        'u19' => 'U19',
                        'u17' => 'U17',
                        'u15' => 'U15',
                    ]),

                Tables\Filters\SelectFilter::make('league')
                    ->label('Liga')
                    ->options([
                        'bundesliga' => 'Bundesliga',
                        'regionalliga' => 'Regionalliga',
                        'landesliga' => 'Landesliga',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Alle')
                    ->trueLabel('Nur aktive')
                    ->falseLabel('Nur inaktive'),

                Tables\Filters\SelectFilter::make('season')
                    ->relationship('season', 'name')
                    ->label('Saison'),
            ])
            ->recordActions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('activity_log')
                    ->label('Aktivitäten')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalContent(fn ($record) => view('filament.modals.activity-log', [
                        'activities' => activity()->forSubject($record)->get()
                    ])),
            ])
            ->toolbarActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Exportieren')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn ($records) =>
                            Notification::make()
                                ->title('Export gestartet')
                                ->success()
                                ->send()
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTeams::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Mannschaften';
    }

    public static function getPluralLabel(): string
    {
        return 'Mannschaften';
    }

    public static function getLabel(): string
    {
        return 'Mannschaft';
    }
}
