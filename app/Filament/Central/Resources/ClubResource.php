<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\ClubResource\Pages;
use App\Models\Tenant;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClubResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationLabel = 'Clubs';

    protected static ?string $modelLabel = 'Club';

    protected static ?string $pluralModelLabel = 'Clubs';

    protected static ?int $navigationSort = 1;

    // Eager Loading für Performance
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['planRelation', 'domains']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Grundinformationen')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Subdomain')
                            ->required()
                            ->unique(table: 'tenants', column: 'id', ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Die Subdomain für den Club (z.B. "testclub" für testclub.localhost)')
                            ->disabled(fn (?Tenant $record) => $record !== null)
                            ->validationMessages([
                                'unique' => 'Diese Subdomain ist bereits vergeben. Bitte wähle eine andere oder bearbeite/lösche den existierenden Club.',
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Club Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('club_fifa_id')
                            ->label('FIFA ID')
                            ->numeric()
                            ->helperText('Offizielle FIFA ID des Clubs (kann für Testzwecke dupliziert werden)'),

                        Forms\Components\TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedGlobeAlt),
                    ])
                    ->columns(2),

                Section::make('Adresse')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Straße & Hausnummer')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('postal_code')
                            ->label('PLZ')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('city')
                            ->label('Stadt')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('country')
                            ->label('Land')
                            ->default('Deutschland')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Abonnement')
                    ->schema([
                        Forms\Components\Select::make('plan_id')
                            ->label('Abonnement-Paket')
                            ->relationship('planRelation', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Wählen Sie das Abonnement-Paket für diesen Club'),

                        Forms\Components\Select::make('template_id')
                            ->label('Website-Template')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Wählen Sie das Design-Template für die Website des Clubs'),

                        Forms\Components\DatePicker::make('subscription_start')
                            ->label('Abo-Beginn')
                            ->displayFormat('d.m.Y')
                            ->helperText('Startdatum des Abonnements'),

                        Forms\Components\DatePicker::make('subscription_end')
                            ->label('Abo-Ende')
                            ->displayFormat('d.m.Y')
                            ->helperText('Enddatum des Abonnements'),

                        Forms\Components\DatePicker::make('trial_ends_at')
                            ->label('Testphase endet am')
                            ->displayFormat('d.m.Y')
                            ->helperText('Datum, an dem die Testphase endet'),
                    ])
                    ->columns(2),

                Section::make('Comet API Integration')
                    ->schema([
                        Forms\Components\Placeholder::make('comet_api_info')
                            ->label('API-Daten')
                            ->content(fn (?Tenant $record) => $record && $record->comet_api_data
                                ? 'Daten vorhanden - Letzte Aktualisierung: ' . ($record->updated_at?->format('d.m.Y H:i') ?? 'Unbekannt')
                                : 'Noch keine API-Daten synchronisiert'),

                        Forms\Components\Textarea::make('comet_api_data')
                            ->label('Rohdaten (JSON)')
                            ->rows(5)
                            ->disabled()
                            ->helperText('Automatisch synchronisierte Daten von der Comet API')
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Subdomain')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Club Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('club_fifa_id')
                    ->label('FIFA ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('city')
                    ->label('Stadt')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('planRelation.name')
                    ->label('Abonnement')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Basic' => 'gray',
                        'Pro' => 'success',
                        'Premium' => 'warning',
                        default => 'info',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subscription_start')
                    ->label('Abo-Beginn')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('subscription_end')
                    ->label('Abo-Ende')
                    ->date('d.m.Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state && $state->isFuture() ? 'success' : 'danger')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('comet_api_data')
                    ->label('API-Sync')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn (Tenant $record) => !empty($record->comet_api_data))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aktualisiert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('Abonnement')
                    ->relationship('planRelation', 'name'),

                Tables\Filters\Filter::make('has_api_data')
                    ->label('Mit API-Daten')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('comet_api_data')),

                Tables\Filters\Filter::make('subscription_active')
                    ->label('Aktives Abo')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('subscription_end', '>=', now())
                              ->orWhereNull('subscription_end')
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('sync_comet_api')
                    ->label('API Sync')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('info')
                    ->action(function (Tenant $record) {
                        // TODO: Implement Comet API sync logic
                        \Filament\Notifications\Notification::make()
                            ->title('API-Synchronisation')
                            ->body('Die API-Synchronisation für ' . $record->name . ' wurde gestartet.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListClubs::route('/'),
            'create' => Pages\CreateClub::route('/create'),
            'view' => Pages\ViewClub::route('/{record}'),
            'edit' => Pages\EditClub::route('/{record}/edit'),
        ];
    }
}
