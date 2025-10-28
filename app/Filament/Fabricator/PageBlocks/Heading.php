<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class Heading extends PageBlock
{
    protected static string $name = 'heading';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('content')
                    ->label('Überschrift')
                    ->required(),
                Select::make('level')
                    ->label('Größe')
                    ->options([
                        'h1' => 'H1 - Sehr groß',
                        'h2' => 'H2 - Groß',
                        'h3' => 'H3 - Mittel',
                        'h4' => 'H4 - Klein',
                    ])
                    ->default('h2')
                    ->required(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
