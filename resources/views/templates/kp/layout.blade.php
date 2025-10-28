<!DOCTYPE html>
<html lang="de">
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
</head>
<body class="min-h-screen bg-gray-50">
    @include('templates.kp.partials.navbar')

    @yield('hero')

    <main class="py-12">
        <div class="container mx-auto px-4">
            @yield('content')
        </div>
    </main>

    @include('templates.kp.partials.footer')
</body>
</html>
