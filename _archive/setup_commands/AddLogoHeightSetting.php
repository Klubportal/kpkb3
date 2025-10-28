<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddLogoHeightSetting extends Command
{
    protected $signature = 'settings:add-logo-height';
    protected $description = 'Add logo_height setting to general settings';

    public function handle()
    {
        DB::connection('central')->table('settings')->insert([
            'group' => 'general',
            'name' => 'logo_height',
            'locked' => false,
            'payload' => json_encode('3.5rem'),
        ]);

        $this->info('Logo height setting added successfully!');
        return 0;
    }
}
