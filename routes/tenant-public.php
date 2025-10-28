<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Tenant Public Routes (No Authentication)
|--------------------------------------------------------------------------
|
| These routes are accessible on tenant domains without authentication.
| Used for: Storage files (logos, media), public assets, etc.
|
*/

// ========================================
// 📁 TEST ROUTE FOR DEBUGGING
// ========================================
Route::get('/test-storage-debug', function () {
    $host = request()->getHost();
    $domain = explode('.', $host)[0];

    return response()->json([
        'host' => $host,
        'domain' => $domain,
        'tenant_model_exists' => class_exists(\App\Models\Tenant::class),
        'tenant_found' => \App\Models\Tenant::find($domain) ? 'yes' : 'no',
    ]);
});

// ========================================
// 📁 TENANT STORAGE ROUTE (PUBLIC - NO AUTH)
// ========================================
Route::get('/storage/{path}', function ($path) {
    try {
        // Security: Only allow specific directories
        $allowedPaths = ['logos', 'branding', 'media', 'uploads'];
        $firstSegment = explode('/', $path)[0];

        if (!in_array($firstSegment, $allowedPaths)) {
            return response('Access denied', 403);
        }

        // Get tenant from domain
        $host = request()->getHost();
        $domain = explode('.', $host)[0];

        $tenant = \App\Models\Tenant::find($domain);

        if (!$tenant) {
            return response('Tenant not found', 404);
        }

        // Build file path
        $storagePath = storage_path("tenant{$tenant->id}/app/public/{$path}");

        if (!file_exists($storagePath)) {
            return response('File not found: ' . $storagePath, 404);
        }

        // Get mime type
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Read file
        $file = file_get_contents($storagePath);

        return response($file, 200)->header('Content-Type', $mimeType)->header('Cache-Control', 'public, max-age=31536000');
    } catch (\Throwable $e) {
        return response('Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine(), 500);
    }
})->where('path', '.*')->name('tenant.storage');
