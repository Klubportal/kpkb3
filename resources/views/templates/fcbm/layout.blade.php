<!DOCTYPE html>
<html lang="de" x-data="{ mobileMenuOpen: false }">
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

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: {{ $settings->primary_color ?? '#DC052D' }};
            --secondary-color: {{ $settings->secondary_color ?? '#0066B2' }};
            --accent-color: {{ $settings->accent_color ?? '#FCBF49' }};
            --header-bg: {{ $settings->header_bg_color ?? '#FFFFFF' }};
            --header-text: {{ $settings->header_text_color ?? '#1F2937' }};
            --hero-bg: {{ $settings->hero_bg_color ?? '#DC052D' }};
            --hero-text: {{ $settings->hero_text_color ?? '#FFFFFF' }};
            --badge-bg: {{ $settings->badge_bg_color ?? '#DC052D' }};
            --badge-text: {{ $settings->badge_text_color ?? '#FFFFFF' }};
            --footer-bg: {{ $settings->footer_bg_color ?? '#1A1A1A' }};
            --footer-text: {{ $settings->footer_text_color ?? '#FFFFFF' }};
        }

        /* Dynamic Color Classes */
        .bg-primary { background-color: var(--primary-color) !important; }
        .text-primary { color: var(--primary-color) !important; }
        .border-primary { border-color: var(--primary-color) !important; }
        .hover\:bg-primary:hover { background-color: var(--primary-color) !important; }
        .hover\:text-primary:hover { color: var(--primary-color) !important; }

        .bg-secondary { background-color: var(--secondary-color) !important; }
        .text-secondary { color: var(--secondary-color) !important; }

        .bg-accent { background-color: var(--accent-color) !important; }
        .text-accent { color: var(--accent-color) !important; }

        .bg-header { background-color: var(--header-bg) !important; }
        .text-header { color: var(--header-text) !important; }

        .bg-hero { background-color: var(--hero-bg) !important; }
        .text-hero { color: var(--hero-text) !important; }

        .bg-badge { background-color: var(--badge-bg) !important; }
        .text-badge { color: var(--badge-text) !important; }

        .bg-footer { background-color: var(--footer-bg) !important; }
        .text-footer { color: var(--footer-text) !important; }

        /* Gradient Classes */
        .gradient-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        /* Swiper Custom Styles */
        .hero-swiper {
            height: 600px;
        }

        .hero-swiper .swiper-slide {
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .hero-swiper .slide-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 3rem;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            color: white;
        }

        .hero-swiper .swiper-button-next,
        .hero-swiper .swiper-button-prev {
            color: white;
        }

        .hero-swiper .swiper-pagination-bullet-active {
            background: var(--primary-color);
        }

        @media (max-width: 768px) {
            .hero-swiper {
                height: 400px;
            }

            .hero-swiper .slide-content {
                padding: 1.5rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased">
    @include('templates.fcbm.partials.navbar')

    @yield('hero')

    <main>
        @yield('content')
    </main>

    @include('templates.fcbm.partials.footer')

    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
