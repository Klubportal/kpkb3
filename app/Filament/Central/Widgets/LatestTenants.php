<?php

namespace App\Filament\Central\Widgets;

use App\Models\Central\Tenant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTenants extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()
                    ->with('plan')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('Tenant ID'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label(__('Plan'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Free' => 'gray',
                        'Basic' => 'warning',
                        'Pro' => 'info',
                        'Enterprise' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label(__('Trial Ends'))
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state && $state < now()->addDays(7) ? 'danger' : 'warning')
                    ->placeholder(__('N/A')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->heading(__('Latest Tenants'))
            ->description(__('Most recently registered clubs'));
    }
}
