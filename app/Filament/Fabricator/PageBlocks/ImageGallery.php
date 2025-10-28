<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class ImageGallery extends PageBlock
{
    protected static string $name = 'image-gallery';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                FileUpload::make('images')
                    ->label('Bilder')
                    ->multiple()
                    ->image()
                    ->maxFiles(10)
                    ->columnSpanFull(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
