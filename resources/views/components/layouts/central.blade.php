@php
    $settings = app(\App\Settings\GeneralSettings::class);
    $theme = app(\App\Settings\ThemeSettings::class);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? $settings->site_name }}</title>
    <meta name="description" content="{{ $settings->site_description }}">

    @if($settings->favicon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($settings->favicon) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --primary-color: {{ $settings->primary_color }};
            --secondary-color: {{ $settings->secondary_color }};
        }
        body {
            font-family: {{ $settings->font_family }}, ui-sans-serif, system-ui, sans-serif;
            font-size: {{ $settings->font_size }}px;
            color: {{ $theme->text_color }};
        }
        .header-nav {
            background-color: {{ $theme->header_bg_color }} !important;
            color: {{ $theme->text_color }};
        }
        .footer-section {
            background-color: {{ $theme->footer_bg_color }} !important;
            color: {{ $theme->text_color }};
        }
        a:not(.btn):hover {
            color: {{ $theme->link_color }} !important;
        }
        .btn-primary {
            background-color: {{ $settings->primary_color }} !important;
            border-color: {{ $settings->primary_color }} !important;
            color: white !important;
        }
        .btn-primary:hover {
            background-color: {{ $settings->secondary_color }} !important;
            border-color: {{ $settings->secondary_color }} !important;
        }
    </style>
</head>
<body class="font-sans antialiasing">

    {{-- Navigation --}}
    <x-mary-nav sticky class="lg:hidden header-nav border-b border-base-300">
        <x-slot:brand>
            <div class="flex items-center gap-2">
                @if($settings->logo)
                    <img src="{{ Storage::url($settings->logo) }}" alt="{{ $settings->site_name }}" class="h-8 w-auto">
                @else
                    <span class="text-2xl">⚽</span>
                @endif
                <span class="font-bold text-xl">{{ $settings->site_name }}</span>
            </div>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-mary-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-mary-nav>

    {{-- Desktop Navigation --}}
    <div class="hidden lg:block header-nav border-b border-base-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    @if($settings->logo)
                        <img src="{{ Storage::url($settings->logo) }}" alt="{{ $settings->site_name }}" class="h-10 w-auto">
                    @else
                        <span class="text-2xl">⚽</span>
                    @endif
                    <span class="font-bold text-xl">{{ $settings->site_name }}</span>
                </div>

                <div class="flex items-center gap-6">
                    <a href="{{ url('/') }}" class="hover:text-primary transition">Home</a>
                    <a href="{{ url('/news') }}" class="hover:text-primary transition">News</a>
                    <a href="{{ url('/pages') }}" class="hover:text-primary transition">Pages</a>
                    <a href="{{ url('/admin') }}" class="btn btn-primary btn-sm">Admin Login</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Drawer for mobile --}}
    <x-mary-drawer id="main-drawer" class="lg:hidden">
        <div class="p-4">
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-4">
                    @if($settings->logo)
                        <img src="{{ Storage::url($settings->logo) }}" alt="{{ $settings->site_name }}" class="h-8 w-auto">
                    @else
                        <span class="text-2xl">⚽</span>
                    @endif
                    <span class="font-bold text-xl">{{ $settings->site_name }}</span>
                </div>
            </div>
            <x-mary-menu>
                <x-mary-menu-item title="Home" icon="o-home" link="{{ url('/') }}" />
                <x-mary-menu-item title="News" icon="o-newspaper" link="{{ url('/news') }}" />
                <x-mary-menu-item title="Pages" icon="o-document-text" link="{{ url('/pages') }}" />
                <x-mary-menu-separator />
                <x-mary-menu-item title="Admin Login" icon="o-arrow-right-on-rectangle" link="{{ url('/admin') }}" />
            </x-mary-menu>
        </div>
    </x-mary-drawer>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="footer-section border-t border-base-300 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        @if($settings->logo)
                            <img src="{{ Storage::url($settings->logo) }}" alt="{{ $settings->site_name }}" class="h-8 w-auto">
                        @else
                            <span class="text-2xl">⚽</span>
                        @endif
                        <span class="font-bold text-lg">{{ $settings->site_name }}</span>
                    </div>
                    <p class="text-sm text-base-content/70">
                        {{ $settings->site_description }}
                    </p>
                </div>

                <div>
                    <h3 class="font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/') }}" class="hover:text-primary">Home</a></li>
                        <li><a href="{{ url('/news') }}" class="hover:text-primary">News</a></li>
                        <li><a href="{{ url('/pages') }}" class="hover:text-primary">Pages</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-bold mb-4">Kontakt</h3>
                    <p class="text-sm text-base-content/70">
                        @if($settings->contact_email)
                            E-Mail: <a href="mailto:{{ $settings->contact_email }}">{{ $settings->contact_email }}</a><br>
                        @endif
                        @if($settings->phone)
                            Tel: {{ $settings->phone }}<br>
                        @endif
                        <span class="text-xs mt-2 block">Powered by Laravel & Filament</span>
                    </p>
                </div>
            </div>

            <div class="border-t border-base-300 mt-8 pt-8 text-center text-sm text-base-content/70">
                © {{ date('Y') }} {{ $settings->site_name }}. All rights reserved.
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
