<?php

namespace App\Filament\Central\Resources;

use Z3d0X\FilamentFabricator\Resources\PageResource as BaseFabricatorPageResource;

class FabricatorPageResource extends BaseFabricatorPageResource
{
    protected static bool $shouldRegisterNavigation = false;
}
