<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Adding FCBM template to database...\n\n";

try {
    // Check if template already exists
    $exists = DB::connection('central')->table('templates')->where('slug', 'fcbm')->exists();

    if ($exists) {
        echo "⚠ Template 'fcbm' already exists. Updating...\n";

        DB::connection('central')->table('templates')->where('slug', 'fcbm')->update([
            'name' => 'FC Bayern Modern',
            'description' => 'Modernes Template im Stil großer Fußballvereine mit Hero-Slider, dynamischen Farben und responsivem Design',
            'layout_path' => 'templates.fcbm.layout',
            'is_active' => 1,
            'features' => json_encode([
                'hero_slider' => true,
                'news_grid' => true,
                'match_preview' => true,
                'table_widget' => true,
                'social_media' => true,
                'sponsors' => true
            ]),
            'colors' => json_encode([
                'primary' => '#DC052D',
                'secondary' => '#0066B2',
                'accent' => '#FCBF49'
            ]),
            'updated_at' => now()
        ]);

        echo "✓ Template 'fcbm' updated successfully!\n";
    } else {
        // Get max sort_order
        $maxSort = DB::connection('central')->table('templates')->max('sort_order') ?? 0;

        DB::connection('central')->table('templates')->insert([
            'name' => 'FC Bayern Modern',
            'display_name' => 'FC Bayern Modern',
            'slug' => 'fcbm',
            'description' => 'Modernes Template im Stil großer Fußballvereine mit Hero-Slider, dynamischen Farben und responsivem Design',
            'layout_path' => 'templates.fcbm.layout',
            'is_active' => 1,
            'is_default' => 0,
            'sort_order' => $maxSort + 1,
            'features' => json_encode([
                'hero_slider' => true,
                'news_grid' => true,
                'match_preview' => true,
                'table_widget' => true,
                'social_media' => true,
                'sponsors' => true
            ]),
            'colors' => json_encode([
                'primary' => '#DC052D',
                'secondary' => '#0066B2',
                'accent' => '#FCBF49'
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "✓ Template 'fcbm' added successfully!\n";
    }

    // Show all templates
    echo "\nAll templates:\n";
    $templates = DB::connection('central')->table('templates')->orderBy('sort_order')->get(['id', 'name', 'slug', 'is_active', 'is_default']);
    foreach ($templates as $template) {
        $status = $template->is_default ? ' [DEFAULT]' : '';
        $active = $template->is_active ? '✓' : '✗';
        echo "  {$active} ID: {$template->id} | {$template->name} ({$template->slug}){$status}\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n✓ Done!\n";
