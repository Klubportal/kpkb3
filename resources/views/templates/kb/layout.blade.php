<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Klubportal'))</title>

    @php
        $clubSettings = app(\App\Settings\Tenant\ClubSettings::class);

        // Try to get central settings, fallback to null if not available
        try {
            $centralSettings = app(\App\Settings\GeneralSettings::class);
        } catch (\Exception $e) {
            $centralSettings = null;
        }

        $faviconUrl = $clubSettings->favicon
            ? asset('storage/' . $clubSettings->favicon)
            : ($centralSettings && $centralSettings->favicon ? asset('storage/' . $centralSettings->favicon) : asset('favicon.ico'));
    @endphp

    <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ $faviconUrl }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2">
                            <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span class="text-xl font-bold text-gray-900">Klubportal</span>
                        </a>
                    </div>

                    <!-- Navigation -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">
                            Home
                        </a>
                        <a href="{{ route('clubs.register') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">
                            Verein Registrieren
                        </a>
                        <a href="{{ route('filament.landlord.auth.login') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">
                            Login
                        </a>
                        <a href="{{ route('clubs.register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                            Jetzt starten
                        </a>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- About -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                            Über Klubportal
                        </h3>
                        <p class="text-gray-600 text-sm">
                            Die moderne Lösung für Vereinsverwaltung. Einfach, professionell, cloudbasiert.
                        </p>
                    </div>

                    <!-- Links -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                            Produkt
                        </h3>
                        <ul class="space-y-2">
                            <li><a href="#features" class="text-gray-600 hover:text-blue-600 text-sm transition">Features</a></li>
                            <li><a href="#pricing" class="text-gray-600 hover:text-blue-600 text-sm transition">Preise</a></li>
                            <li><a href="#templates" class="text-gray-600 hover:text-blue-600 text-sm transition">Templates</a></li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                            Support
                        </h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm transition">Dokumentation</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm transition">Kontakt</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm transition">FAQ</a></li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                            Rechtliches
                        </h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm transition">Datenschutz</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm transition">Impressum</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-blue-600 text-sm transition">AGB</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-gray-200">
                    <p class="text-center text-gray-500 text-sm">
                        &copy; {{ date('Y') }} Klubportal. Alle Rechte vorbehalten.
                    </p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
