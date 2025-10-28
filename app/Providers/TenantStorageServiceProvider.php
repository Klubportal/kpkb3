<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class TenantStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services - runs BEFORE other route registrations
     */
    public function boot(): void
    {
        // Register storage route with HIGHEST priority (boot runs early)
        $this->registerStorageRoute();
    }

    protected function registerStorageRoute(): void
    {
        Route::middleware(['web'])->get('/storage/{path}', function ($path) {
            // Get host and check if it's a tenant domain
            $host = request()->getHost();
            $centralDomains = config('tenancy.central_domains', ['localhost', '127.0.0.1']);

            // DEBUG: Log the request
            \Log::info('Storage route hit', [
                'path' => $path,
                'host' => $host,
                'is_central' => in_array($host, $centralDomains),
            ]);

            // Only serve files on tenant domains
            if (in_array($host, $centralDomains)) {
                return response('Storage not available on central domain', 404);
            }

            // Security: Only allow specific directories
            $allowedPaths = ['logos', 'branding', 'media', 'uploads'];
            $firstSegment = explode('/', $path)[0];

            if (!in_array($firstSegment, $allowedPaths)) {
                \Log::warning('Storage: Access denied', ['segment' => $firstSegment]);
                return response('Access denied to: ' . $firstSegment, 403);
            }

            // Get tenant from domain
            $domain = explode('.', $host)[0];

            $tenant = \App\Models\Tenant::find($domain);

            if (!$tenant) {
                \Log::error('Storage: Tenant not found', ['domain' => $domain]);
                return response('Tenant not found: ' . $domain, 404);
            }

            // Build file path
            $storagePath = storage_path("tenant{$tenant->id}/app/public/{$path}");

            if (!file_exists($storagePath)) {
                \Log::error('Storage: File not found', ['path' => $storagePath]);
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

            \Log::info('Storage: Success', ['size' => strlen($file)]);

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=31536000');
        })->where('path', '.*')->name('tenant.storage');
    }
}
