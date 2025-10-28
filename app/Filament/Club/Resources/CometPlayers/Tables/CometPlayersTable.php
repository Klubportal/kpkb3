<?php

namespace App\Filament\Club\Resources\CometPlayers\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class CometPlayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_url')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-player.png'))
                    ->size(40),
                TextColumn::make('shirt_number')
                    ->label('#')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->size('lg'),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(['name', 'first_name', 'last_name'])
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn ($record) => $record->popular_name),
                TextColumn::make('playerGroups.name')
                    ->label('Team/Gruppe')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->color('info'),
                TextColumn::make('date_of_birth')
                    ->label('Geburtsdatum')
                    ->date('d.m.Y')
                    ->sortable()
                    ->description(fn ($record) => $record->age ? $record->age . ' Jahre' : ''),
                TextColumn::make('position')
                    ->label('Position')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'goalkeeper' => 'warning',
                        'defender' => 'info',
                        'midfielder' => 'success',
                        'forward' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'goalkeeper' => 'Torwart',
                        'defender' => 'Verteidiger',
                        'midfielder' => 'Mittelfeld',
                        'forward' => 'Stürmer',
                        default => 'Unbekannt',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'injured' => 'danger',
                        'suspended' => 'warning',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktiv',
                        'injured' => 'Verletzt',
                        'suspended' => 'Gesperrt',
                        'inactive' => 'Inaktiv',
                        default => $state,
                    }),
                TextColumn::make('season_goals')
                    ->label('Tore')
                    ->numeric()
                    ->sortable()
                    ->color('success')
                    ->icon('heroicon-o-trophy')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('season_matches')
                    ->label('Spiele')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->icon('heroicon-o-phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('E-Mail')
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('playerGroups')
                    ->label('Team/Gruppe')
                    ->relationship('playerGroups', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('position')
                    ->label('Position')
                    ->options([
                        'goalkeeper' => 'Torwart',
                        'defender' => 'Verteidiger',
                        'midfielder' => 'Mittelfeld',
                        'forward' => 'Stürmer',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktiv',
                        'injured' => 'Verletzt',
                        'suspended' => 'Gesperrt',
                        'inactive' => 'Inaktiv',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
            ])
            ->defaultSort('shirt_number', 'asc')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100]);
    }
}

