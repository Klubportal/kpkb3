<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Verfügbare Sprachen aus .env
        $availableLocales = explode(',', env('APP_AVAILABLE_LOCALES', 'de,en'));

        // Sprache aus Session, Cookie oder Browser
        $locale = Session::get('locale')
               ?? $request->cookie('locale')
               ?? $request->getPreferredLanguage($availableLocales)
               ?? env('APP_LOCALE', 'de');

        // Sprache validieren
        if (!in_array($locale, $availableLocales)) {
            $locale = env('APP_LOCALE', 'de');
        }

        // Sprache setzen
        App::setLocale($locale);

        return $next($request);
    }
}
