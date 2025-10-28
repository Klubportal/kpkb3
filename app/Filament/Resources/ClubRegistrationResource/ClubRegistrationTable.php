<?php

namespace App\Filament\Resources\ClubRegistrationResource;

use Filament\Tables\Table;
use Filament\Tables;

class ClubRegistrationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('club_name')->searchable(),
                Tables\Columns\TextColumn::make('subdomain'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('template'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Ausstehend',
                        'approved' => 'Freigeschaltet',
                        'rejected' => 'Abgelehnt',
                    ]),
            ]);
    }
}
