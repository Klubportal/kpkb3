<?php

namespace App\Filament\Club\Resources\SyncLogs\Tables;

use Filament\Tables\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SyncLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('sync_type')
                    ->label('Sync Type')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('primary'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'partial' => 'warning',
                        'running' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('records_inserted')
                    ->label('Inserted')
                    ->numeric()
                    ->sortable()
                    ->color('success')
                    ->toggleable(),
                TextColumn::make('records_updated')
                    ->label('Updated')
                    ->numeric()
                    ->sortable()
                    ->color('warning')
                    ->toggleable(),
                TextColumn::make('records_skipped')
                    ->label('Skipped')
                    ->numeric()
                    ->sortable()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('records_failed')
                    ->label('Failed')
                    ->numeric()
                    ->sortable()
                    ->color('danger')
                    ->toggleable(),
                TextColumn::make('total_records')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->suffix('s')
                    ->sortable()
                    ->color('info'),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'partial' => 'Partial',
                        'running' => 'Running',
                    ]),
                SelectFilter::make('sync_type')
                    ->options([
                        'comet_matches' => 'Comet Matches',
                        'comet_rankings' => 'Comet Rankings',
                        'comet_top_scorers' => 'Comet Top Scorers',
                        'comet_all' => 'Comet All',
                        'tenant_sync' => 'Tenant Sync',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->defaultSort('id', 'desc')
            ->poll('30s');
    }
}
