<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClubRegistrationResource\Pages;
use App\Filament\Resources\ClubRegistrationResource\ClubRegistrationForm;
use App\Filament\Resources\ClubRegistrationResource\ClubRegistrationTable;
use App\Models\ClubRegistration;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClubRegistrationResource extends Resource
{
    protected static ?string $model = ClubRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|UnitEnum|null $navigationGroup = 'Vereine';
    protected static ?string $label = 'Vereins-Registrierung';
    protected static ?string $pluralLabel = 'Vereins-Registrierungen';

    public static function form(Schema $schema): Schema
    {
        return ClubRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClubRegistrationTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClubRegistrations::route('/'),
            'edit' => Pages\EditClubRegistration::route('/{record}/edit'),
        ];
    }
}
