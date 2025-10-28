<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\TenantResource\Pages;
use App\Models\Central\Tenant;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Vereine';

    protected static ?string $modelLabel = 'Verein';

    protected static ?string $pluralModelLabel = 'Vereine';

    protected static ?int $navigationSort = 0;

    protected static string|UnitEnum|null $navigationGroup = 'Verwaltung';    // Eager Loading für Performance
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
                            ->label('Subdomain / Vereins-ID')
                            ->required()
                            ->unique(table: 'tenants', column: 'id', ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Die Subdomain für den Verein (z.B. "meinverein" für meinverein.localhost)')
                            ->disabled(fn (?Tenant $record) => $record !== null)
                            ->alphaDash()
                            ->validationMessages([
                                'unique' => 'Diese Subdomain ist bereits vergeben. Bitte wähle eine andere.',
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Vereinsname')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                // Auto-generate subdomain from name if creating new record
                                if (!$get('id') && $state) {
                                    $set('id', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-envelope'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-phone'),
                    ])
                    ->columns(2),

                Section::make('FIFA / COMET Daten')
                    ->description('Pflichtfelder für automatische COMET-Synchronisation')
                    ->schema([
                        Forms\Components\TextInput::make('data.club_fifa_id')
                            ->label('Club FIFA ID')
                            ->required()
                            ->numeric()
                            ->helperText('Eindeutige FIFA ID des Clubs (z.B. 598)')
                            ->prefixIcon('heroicon-o-identification'),

                        Forms\Components\TextInput::make('data.organisation_fifa_id')
                            ->label('Organisation FIFA ID')
                            ->required()
                            ->numeric()
                            ->helperText('FIFA ID der Organisation/Verband (z.B. 1)')
                            ->prefixIcon('heroicon-o-building-library'),

                        Forms\Components\TextInput::make('data.country_code')
                            ->label('Ländercode (ISO 3166-1 alpha-3)')
                            ->required()
                            ->maxLength(3)
                            ->placeholder('HRV')
                            ->helperText('3-stelliger Ländercode (z.B. HRV, GER, AUT)')
                            ->prefixIcon('heroicon-o-flag'),

                        Forms\Components\TextInput::make('data.city')
                            ->label('Stadt')
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-map-pin'),

                        Forms\Components\TextInput::make('data.founded_year')
                            ->label('Gründungsjahr')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(fn () => (int) date('Y'))
                            ->prefixIcon('heroicon-o-calendar'),

                        Forms\Components\TextInput::make('data.website')
                            ->label('Website')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->placeholder('https://example.com'),

                        Forms\Components\TextInput::make('data.logo_url')
                            ->label('Logo URL')
                            ->url()
                            ->prefixIcon('heroicon-o-photo')
                            ->placeholder('https://example.com/logo.png')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed(fn (?Tenant $record) => $record !== null)
                    ->collapsible(),

                Section::make('Admin Benutzer')
                    ->description('Erster Admin-Benutzer für den Tenant')
                    ->schema([
                        Forms\Components\TextInput::make('data.admin_name')
                            ->label('Admin Name')
                            ->required(fn (?Tenant $record) => $record === null)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-user'),

                        Forms\Components\TextInput::make('data.admin_email')
                            ->label('Admin E-Mail')
                            ->email()
                            ->required(fn (?Tenant $record) => $record === null)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-envelope'),

                        Forms\Components\TextInput::make('data.admin_password')
                            ->label('Admin Passwort')
                            ->password()
                            ->required(fn (?Tenant $record) => $record === null)
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Mindestens 8 Zeichen')
                            ->prefixIcon('heroicon-o-lock-closed'),

                        Forms\Components\TextInput::make('data.admin_password_confirmation')
                            ->label('Passwort bestätigen')
                            ->password()
                            ->required(fn (?Tenant $record) => $record === null)
                            ->same('data.admin_password')
                            ->dehydrated(false)
                            ->prefixIcon('heroicon-o-lock-closed'),
                    ])
                    ->columns(2)
                    ->collapsed(fn (?Tenant $record) => $record !== null)
                    ->collapsible()
                    ->hidden(fn (?Tenant $record) => $record !== null),

                Section::make('Abonnement & Status')
                    ->schema([
                        Forms\Components\Select::make('plan_id')
                            ->label('Abonnement-Paket')
                            ->relationship('planRelation', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Wählen Sie das Abonnement-Paket für diesen Verein'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktiv')
                            ->default(true)
                            ->helperText('Inaktive Vereine können sich nicht einloggen'),

                        Forms\Components\DatePicker::make('trial_ends_at')
                            ->label('Testphase endet am')
                            ->displayFormat('d.m.Y')
                            ->helperText('Datum, an dem die Testphase endet')
                            ->native(false),

                        Forms\Components\DatePicker::make('subscription_ends_at')
                            ->label('Abo-Ende')
                            ->displayFormat('d.m.Y')
                            ->helperText('Enddatum des Abonnements')
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Custom Domain')
                    ->schema([
                        Forms\Components\TextInput::make('custom_domain')
                            ->label('Eigene Domain')
                            ->maxLength(255)
                            ->helperText('Optional: Eigene Domain für diesen Verein (z.B. www.meinverein.de)')
                            ->prefixIcon('heroicon-o-globe-alt'),

                        Forms\Components\Toggle::make('custom_domain_verified')
                            ->label('Domain verifiziert')
                            ->disabled()
                            ->helperText('Wird automatisch gesetzt nach erfolgreicher DNS-Verifikation'),

                        Forms\Components\Placeholder::make('custom_domain_status')
                            ->label('Domain-Status')
                            ->content(fn (?Tenant $record) => $record?->custom_domain_status ?? 'Keine Domain konfiguriert'),

                        Forms\Components\DateTimePicker::make('custom_domain_verified_at')
                            ->label('Domain verifiziert am')
                            ->displayFormat('d.m.Y H:i')
                            ->disabled()
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->collapsible(),

                Section::make('Erweiterte Daten')
                    ->schema([
                        Forms\Components\KeyValue::make('data')
                            ->label('Zusätzliche Daten')
                            ->helperText('Hier können zusätzliche Metadaten gespeichert werden')
                            ->reorderable(),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Subdomain')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Zum Kopieren klicken'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Vereinsname')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('planRelation.name')
                    ->label('Paket')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Free' => 'gray',
                        'Basic' => 'info',
                        'Pro' => 'success',
                        'Premium' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('data.club_fifa_id')
                    ->label('FIFA ID')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-identification')
                    ->tooltip('Club FIFA ID für COMET-Sync')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Testphase endet')
                    ->date('d.m.Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => !$state ? 'gray' : ($state->isFuture() ? 'warning' : 'danger'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subscription_ends_at')
                    ->label('Abo-Ende')
                    ->date('d.m.Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => !$state ? 'gray' : ($state->isFuture() ? 'success' : 'danger'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('custom_domain')
                    ->label('Custom Domain')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-globe-alt'),

                Tables\Columns\IconColumn::make('custom_domain_verified')
                    ->label('Domain OK')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->label('Paket')
                    ->relationship('planRelation', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->trueLabel('Nur aktive')
                    ->falseLabel('Nur inaktive')
                    ->native(false),

                Tables\Filters\Filter::make('in_trial')
                    ->label('In Testphase')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereNotNull('trial_ends_at')
                              ->where('trial_ends_at', '>=', now())
                    ),

                Tables\Filters\Filter::make('subscription_active')
                    ->label('Aktives Abo')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('subscription_ends_at', '>=', now())
                              ->orWhereNull('subscription_ends_at')
                    ),
            ])
            ->recordActions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('login')
                    ->label('Als Verein einloggen')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('info')
                    ->url(fn (Tenant $record): string => 'http://' . $record->id . '.localhost:8000/admin')
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
