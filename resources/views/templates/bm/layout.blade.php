<!DOCTYPE html>
<html lang="de" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', mobileMenuOpen: false }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $settings->website_name ?? 'Fußballverein')</title>

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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary-color: {{ $settings->primary_color ?? '#dc2626' }};
            --secondary-color: {{ $settings->secondary_color ?? '#1e40af' }};
            --accent-color: {{ $settings->accent_color ?? '#f59e0b' }};
            --header-bg: {{ $settings->header_bg_color ?? '#ffffff' }};
            --header-text: {{ $settings->header_text_color ?? '#1f2937' }};
            --hero-bg: {{ $settings->hero_bg_color ?? '#dc2626' }};
            --hero-text: {{ $settings->hero_text_color ?? '#ffffff' }};
            --badge-bg: {{ $settings->badge_bg_color ?? '#dc2626' }};
            --badge-text: {{ $settings->badge_text_color ?? '#ffffff' }};
            --footer-bg: {{ $settings->footer_bg_color ?? '#111827' }};
            --footer-text: {{ $settings->footer_text_color ?? '#ffffff' }};
        }
        .bg-primary { background-color: var(--primary-color) !important; }
        .text-primary { color: var(--primary-color) !important; }
        .border-primary { border-color: var(--primary-color) !important; }
        .hover\:text-primary:hover { color: var(--primary-color) !important; }
        .hover\:bg-primary:hover { background-color: var(--primary-color) !important; }
        .from-primary { --tw-gradient-from: var(--primary-color) var(--tw-gradient-from-position) !important; }
        .to-primary { --tw-gradient-to: var(--primary-color) var(--tw-gradient-to-position) !important; }
        .bg-secondary { background-color: var(--secondary-color) !important; }
        .text-secondary { color: var(--secondary-color) !important; }

        /* Header Farben */
        .header-bg { background-color: var(--header-bg) !important; }
        .header-text { color: var(--header-text) !important; }

        /* Hero Farben */
        .hero-bg { background-color: var(--hero-bg) !important; }
        .hero-text { color: var(--hero-text) !important; }

        /* Badge Farben */
        .badge-bg { background-color: var(--badge-bg) !important; }
        .badge-text { color: var(--badge-text) !important; }

        /* Footer Farben */
        .footer-bg { background-color: var(--footer-bg) !important; }
        .footer-text { color: var(--footer-text) !important; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    @include('templates.bm.partials.navbar')
    @yield('hero')
    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>
    @include('templates.bm.partials.footer')
</body>
</html>
