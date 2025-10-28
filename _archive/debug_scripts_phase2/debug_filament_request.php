<?php

// Debug script to capture Filament request parameters
// Add this to your LanguageLine model temporarily to see what Filament sends

// In app/Models/LanguageLine.php, add to booted() method:
/*
static::addGlobalScope('debug', function (Builder $builder) {
    $request = request();

    // Log ALL request data
    \Log::info('=== FILAMENT REQUEST DEBUG ===');
    \Log::info('All Input:', $request->all());
    \Log::info('Query String:', $request->query());
    \Log::info('Request Method:', $request->method());
    \Log::info('Request Path:', $request->path());

    // Check specific parameters
    \Log::info('tableSearch:', $request->input('tableSearch'));
    \Log::info('components.0.tableSearch:', $request->input('components.0.tableSearch'));
    \Log::info('search:', $request->input('search'));
    \Log::info('filter:', $request->input('filter'));
});
*/

echo "Add the code above to your LanguageLine model's booted() method\n";
echo "Then search in Translation Manager and check storage/logs/laravel.log\n";
