<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class CallToAction extends PageBlock
{
    protected static string $name = 'call-to-action';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                //
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
