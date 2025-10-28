<?php

namespace App\Filament\Club\Resources\SyncLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class SyncLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sync_type')
                    ->label('Sync Type')
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->size('lg'),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'partial' => 'warning',
                        'running' => 'info',
                        default => 'gray',
                    }),
                TextEntry::make('tenant_id')
                    ->label('Tenant ID'),
                TextEntry::make('records_inserted')
                    ->label('Records Inserted')
                    ->numeric()
                    ->color('success'),
                TextEntry::make('records_updated')
                    ->label('Records Updated')
                    ->numeric()
                    ->color('warning'),
                TextEntry::make('records_skipped')
                    ->label('Records Skipped')
                    ->numeric()
                    ->color('gray'),
                TextEntry::make('records_failed')
                    ->label('Records Failed')
                    ->numeric()
                    ->color('danger'),
                TextEntry::make('total_records')
                    ->label('Total Records')
                    ->numeric()
                    ->weight(FontWeight::Bold),
                TextEntry::make('records_processed')
                    ->label('Records Processed')
                    ->numeric(),
                TextEntry::make('started_at')
                    ->label('Started At')
                    ->dateTime('Y-m-d H:i:s'),
                TextEntry::make('completed_at')
                    ->label('Completed At')
                    ->dateTime('Y-m-d H:i:s')
                    ->placeholder('Not completed yet'),
                TextEntry::make('duration_seconds')
                    ->label('Duration (seconds)')
                    ->suffix('s')
                    ->placeholder('-')
                    ->color('info'),
                TextEntry::make('error_message')
                    ->label('Error Message')
                    ->placeholder('No errors')
                    ->color('danger')
                    ->columnSpanFull()
                    ->visible(fn ($record) => !empty($record->error_message)),
                TextEntry::make('error_details')
                    ->label('Error Details')
                    ->placeholder('No error details')
                    ->columnSpanFull()
                    ->visible(fn ($record) => !empty($record->error_details)),
                TextEntry::make('sync_metadata')
                    ->label('Sync Metadata')
                    ->placeholder('No metadata')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '-')
                    ->columnSpanFull()
                    ->copyable()
                    ->copyMessage('Metadata copied!'),
                TextEntry::make('sync_params')
                    ->label('Sync Parameters')
                    ->placeholder('No parameters')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '-')
                    ->columnSpanFull()
                    ->copyable()
                    ->copyMessage('Parameters copied!'),
            ])
            ->columns(3);
    }
}
