<?php

namespace App\Filament\Club\Resources\SyncLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SyncLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->required(),
                TextInput::make('sync_type')
                    ->required(),
                Select::make('status')
                    ->options(['success' => 'Success', 'failed' => 'Failed', 'partial' => 'Partial', 'running' => 'Running'])
                    ->default('running')
                    ->required(),
                TextInput::make('records_processed')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('records_inserted')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('records_updated')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('records_skipped')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('records_failed')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_records')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('completed_at'),
                TextInput::make('duration_seconds')
                    ->numeric()
                    ->default(null),
                Textarea::make('error_message')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('error_details')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('sync_params')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('sync_metadata')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
