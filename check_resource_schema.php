<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Filament\Club\Resources\TemplateSettingResource;
use Stancl\Tenancy\Facades\Tenancy;

echo "\n=== Template Resource Schema Check ===\n\n";

try {
    $tenant = Tenant::find('nknapijed');
    if (!$tenant) {
        echo "❌ Tenant not found!\n";
        exit(1);
    }

    echo "✓ Tenant found: {$tenant->id}\n";
    Tenancy::initialize($tenant);
    echo "✓ Tenancy initialized\n\n";

    // Check if Resource class exists
    if (!class_exists(TemplateSettingResource::class)) {
        echo "❌ TemplateSettingResource class not found!\n";
        exit(1);
    }
    echo "✓ TemplateSettingResource class exists\n";

    // Try to get the schema
    try {
        $resource = new TemplateSettingResource();
        echo "✓ Resource instantiated\n";

        // Get form schema
        $schema = TemplateSettingResource::form(\Filament\Schemas\Schema::make());
        echo "✓ Schema retrieved\n";

        // Count components
        $components = $schema->getComponents();
        echo "\nSchema Components: " . count($components) . "\n";

        foreach ($components as $component) {
            echo "  - " . get_class($component) . "\n";

            // If it's a Tabs component, show tabs
            if ($component instanceof \Filament\Schemas\Components\Tabs) {
                echo "    Tabs:\n";
                $tabs = $component->getTabs();
                foreach ($tabs as $tab) {
                    echo "      - " . $tab->getLabel() . "\n";
                }
            }
        }

    } catch (\Exception $e) {
        echo "\n❌ Error getting schema: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
