<?php

namespace App\Filament\Club\Resources\CometPlayers;

use App\Filament\Club\Resources\CometPlayers\Pages\CreateCometPlayer;
use App\Filament\Club\Resources\CometPlayers\Pages\EditCometPlayer;
use App\Filament\Club\Resources\CometPlayers\Pages\ListCometPlayers;
use App\Filament\Club\Resources\CometPlayers\Pages\ViewCometPlayer;
use App\Filament\Club\Resources\CometPlayers\Schemas\CometPlayerForm;
use App\Filament\Club\Resources\CometPlayers\Schemas\CometPlayerInfolist;
use App\Filament\Club\Resources\CometPlayers\Tables\CometPlayersTable;
use App\Models\Comet\CometPlayer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CometPlayerResource extends Resource
{
    protected static ?string $model = CometPlayer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Spieler';

    protected static ?string $navigationLabel = 'Alle Spieler';

    protected static ?string $modelLabel = 'Spieler';

    protected static ?string $pluralModelLabel = 'Spieler';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CometPlayerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CometPlayerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CometPlayersTable::configure($table);
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
            'index' => ListCometPlayers::route('/'),
            'create' => CreateCometPlayer::route('/create'),
            'view' => ViewCometPlayer::route('/{record}'),
            'edit' => EditCometPlayer::route('/{record}/edit'),
        ];
    }
}
