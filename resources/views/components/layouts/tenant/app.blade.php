<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" x-data="darkMode">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS via CDN (Multi-Tenant kompatibel) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- DaisyUI via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" type="text/css" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Dynamic Theme CSS Variables -->
    @php
        $themeService = app(\App\Services\FrontendThemeService::class);
        $themeData = $themeService->getThemeData();
    @endphp
    <style>
        {!! $themeData['css_variables'] !!}

        /* Custom Tailwind Config */
        @layer base {
            :root {
                --theme-header-bg: {{ $themeData['settings']->header_bg_color ?? '#1e40af' }};
                --theme-footer-bg: {{ $themeData['settings']->footer_bg_color ?? '#1f2937' }};
                --theme-text: {{ $themeData['settings']->text_color ?? '#1f2937' }};
                --theme-link: {{ $themeData['settings']->link_color ?? '#2563eb' }};
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        .hover-lift {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>

    @livewireStyles
</head>
<body class="min-h-screen bg-base-200 antialiased" x-cloak>

    {{-- HEADER --}}
    <header class="sticky top-0 z-50 navbar-scroll transition-all duration-300" style="background-color: var(--theme-header-bg);">
        <div class="navbar {{ $themeData['layout_class'] ?? 'max-w-7xl mx-auto' }} px-4">
            {{-- Logo & Club Name --}}
            <div class="navbar-start">
                <a href="/" class="flex items-center gap-3 hover-scale">
                    @if($logo ?? null)
                        <img src="{{ $logo }}" alt="Logo" class="h-12 w-auto">
                    @endif
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-white">{{ $clubName ?? 'Fußballverein' }}</span>
                        <span class="text-xs text-white/80">{{ $clubTagline ?? 'Tradition & Leidenschaft' }}</span>
                    </div>
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1 gap-1">
                    <li><a href="/" wire:navigate class="font-medium text-white hover:bg-white/20 {{ $themeData['button_style'] ?? 'rounded-lg' }}">Home</a></li>
                    <li><a href="/news" wire:navigate class="font-medium text-white hover:bg-white/20 {{ $themeData['button_style'] ?? 'rounded-lg' }}">News</a></li>
                    <li><a href="/events" wire:navigate class="font-medium text-white hover:bg-white/20 {{ $themeData['button_style'] ?? 'rounded-lg' }}">Termine</a></li>
                    {{-- Dynamic Pages from Menu --}}
                    @php
                        $menuPages = \App\Models\Tenant\Page::query()
                            ->inMenu()
                            ->orderBy('order')
                            ->get();
                    @endphp
                    @foreach($menuPages as $menuPage)
                        <li><a href="/{{ $menuPage->slug }}" wire:navigate class="font-medium text-white hover:bg-white/20 {{ $themeData['button_style'] ?? 'rounded-lg' }}">
                            {{ $menuPage->menu_title ?? $menuPage->title }}
                        </a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Actions --}}
            <div class="navbar-end gap-2">
                {{-- Dark Mode Toggle --}}
                <button @click="toggle" class="btn btn-ghost btn-circle text-white">
                    <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>

                {{-- Search Button --}}
                <button class="btn btn-ghost btn-circle text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>

                {{-- Login Button --}}
                @guest
                    <a href="/login" class="btn btn-sm bg-white/20 text-white hover:bg-white/30 border-0 {{ $themeData['button_style'] ?? 'rounded-lg' }}">Login</a>
                @endguest

                @auth
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full bg-white/20 text-white flex items-center justify-center font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </label>
                        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                            <li><a href="/dashboard">Dashboard</a></li>
                            <li>
                                <form method="POST" action="/logout">
                                    @csrf
                                    <button type="submit" class="w-full text-left">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth

                {{-- Mobile Menu Toggle --}}
                <div class="dropdown dropdown-end lg:hidden">
                    <label tabindex="0" class="btn btn-ghost btn-circle text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a href="/" wire:navigate>Home</a></li>
                        <li><a href="/news" wire:navigate>News</a></li>
                        <li><a href="/events" wire:navigate>Termine</a></li>
                        @foreach($menuPages as $menuPage)
                            <li><a href="/{{ $menuPage->slug }}" wire:navigate>
                                {{ $menuPage->menu_title ?? $menuPage->title }}
                            </a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="min-h-[calc(100vh-20rem)]">
        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="mt-16" style="background-color: var(--theme-footer-bg); color: white;">
        <div class="{{ $themeData['layout_class'] ?? 'max-w-7xl mx-auto' }} px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- Club Info --}}
                <div class="space-y-4 animate-fadeInUp">
                    <h3 class="text-lg font-bold">{{ $clubName ?? 'Fußballverein' }}</h3>
                    <p class="text-sm opacity-80">
                        Tradition seit 1965<br>
                        Mitglied im DFB & Landesverband
                    </p>
                    <div class="flex gap-3">
                        @php
                            try {
                                $socialSettings = app(\App\Settings\SocialMediaSettings::class);
                            } catch (\Exception $e) {
                                $socialSettings = null;
                            }
                        @endphp
                        @if($socialSettings?->facebook_url)
                        <a href="{{ $socialSettings->facebook_url }}" target="_blank" class="btn btn-sm btn-circle btn-ghost hover-scale">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        @endif
                        @if($socialSettings?->instagram_url)
                        <a href="{{ $socialSettings->instagram_url }}" target="_blank" class="btn btn-sm btn-circle btn-ghost hover-scale">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="space-y-4 animate-fadeInUp" style="animation-delay: 0.1s">
                    <h3 class="text-lg font-bold">Schnelllinks</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/news" class="opacity-80 hover:opacity-100 transition">News</a></li>
                        <li><a href="/events" class="opacity-80 hover:opacity-100 transition">Termine</a></li>
                        <li><a href="/teams" class="opacity-80 hover:opacity-100 transition">Teams</a></li>
                        <li><a href="/contact" class="opacity-80 hover:opacity-100 transition">Kontakt</a></li>
                    </ul>
                </div>

                {{-- Info Links --}}
                <div class="space-y-4 animate-fadeInUp" style="animation-delay: 0.2s">
                    <h3 class="text-lg font-bold">Informationen</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/impressum" class="opacity-80 hover:opacity-100 transition">Impressum</a></li>
                        <li><a href="/datenschutz" class="opacity-80 hover:opacity-100 transition">Datenschutz</a></li>
                        <li><a href="/agb" class="opacity-80 hover:opacity-100 transition">AGB</a></li>
                        <li><a href="/satzung" class="opacity-80 hover:opacity-100 transition">Satzung</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div class="space-y-4 animate-fadeInUp" style="animation-delay: 0.3s">
                    @php
                        try {
                            $contactSettings = app(\App\Settings\ContactSettings::class);
                        } catch (\Exception $e) {
                            $contactSettings = null;
                        }
                    @endphp
                    <h3 class="text-lg font-bold">Kontakt</h3>
                    <ul class="space-y-2 text-sm opacity-80">
                        <li>{{ $contactSettings?->street ?? 'Sportplatzstraße 1' }}</li>
                        <li>{{ $contactSettings?->postal_code ?? '12345' }} {{ $contactSettings?->city ?? 'Musterstadt' }}</li>
                        <li class="pt-2">Tel: {{ $contactSettings?->phone ?? '0123 / 456789' }}</li>
                        <li>E-Mail: {{ $contactSettings?->email ?? 'info@testclub.de' }}</li>
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="border-t border-white/20 mt-8 pt-8 text-center text-sm opacity-60">
                <p>&copy; {{ date('Y') }} {{ $clubName ?? 'Fußballverein' }}. Alle Rechte vorbehalten.</p>
                <p class="mt-2">Powered by <a href="https://klubportal.com" class="hover:underline" style="color: var(--theme-link);">Klubportal</a></p>
            </div>
        </div>
    </footer>

    {{-- Back to Top Button --}}
    <button id="back-to-top"
            onclick="scrollToTop()"
            class="fixed bottom-8 right-8 btn btn-circle btn-primary shadow-lg hidden hover-lift z-40 no-print">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
        </svg>
    </button>

    @livewireScripts

    {{-- Toast Notifications --}}
    <x-mary-toast />
</body>
</html>
