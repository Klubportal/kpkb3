<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TextContent extends PageBlock
{
    protected static string $name = 'text-content';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                RichEditor::make('content')
                    ->label('Text')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
