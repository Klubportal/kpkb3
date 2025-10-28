<?php

namespace App\Filament\Club\Resources\SyncLogs;

use App\Filament\Club\Resources\SyncLogs\Pages\CreateSyncLog;
use App\Filament\Club\Resources\SyncLogs\Pages\EditSyncLog;
use App\Filament\Club\Resources\SyncLogs\Pages\ListSyncLogs;
use App\Filament\Club\Resources\SyncLogs\Pages\ViewSyncLog;
use App\Filament\Club\Resources\SyncLogs\Schemas\SyncLogForm;
use App\Filament\Club\Resources\SyncLogs\Schemas\SyncLogInfolist;
use App\Filament\Club\Resources\SyncLogs\Tables\SyncLogsTable;
use App\Models\SyncLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SyncLogResource extends Resource
{
    protected static ?string $model = SyncLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Sync Logs';

    protected static ?string $modelLabel = 'Sync Log';

    protected static ?string $pluralModelLabel = 'Sync Logs';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'sync_type';

    public static function form(Schema $schema): Schema
    {
        return SyncLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SyncLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SyncLogsTable::configure($table);
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
            'index' => ListSyncLogs::route('/'),
            'view' => ViewSyncLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
