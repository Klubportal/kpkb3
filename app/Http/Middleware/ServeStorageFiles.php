<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServeStorageFiles
{
    /**
     * Handle an incoming request - intercept /storage/ URLs BEFORE routing
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is a storage request
        if ($request->is('storage/*')) {
            return $this->serveStorageFile($request);
        }

        return $next($request);
    }

    protected function serveStorageFile(Request $request): Response
    {
        $path = $request->path();
        $path = str_replace('storage/', '', $path);

        // Security: Only allow specific directories
        $allowedPaths = ['logos', 'branding', 'media', 'uploads'];
        $firstSegment = explode('/', $path)[0];

        if (!in_array($firstSegment, $allowedPaths)) {
            return response('Access denied', 403);
        }

        // Get tenant from domain
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', ['localhost', '127.0.0.1']);

        // Only serve on tenant domains
        if (in_array($host, $centralDomains)) {
            return response('Storage not available on central domain', 404);
        }

        $domain = explode('.', $host)[0];

        $tenant = \App\Models\Tenant::find($domain);

        if (!$tenant) {
            return response('Tenant not found', 404);
        }

        // Build file path
        $storagePath = storage_path("tenant{$tenant->id}/app/public/{$path}");

        if (!file_exists($storagePath)) {
            return response('File not found', 404);
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

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }
}
