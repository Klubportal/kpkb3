<div x-data="{ mobileMenu: false }" style="--primary-color: {{ $themeSettings->header_bg_color ?? '#dc2626' }}; --secondary-color: {{ $themeSettings->footer_bg_color ?? '#1f2937' }};">
    {{-- Navigation --}}
    <nav class="navbar bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto">
            <div class="flex-1">
                <a href="/" class="btn btn-ghost text-xl">
                    @if($generalSettings->logo)
                        <img src="{{ asset('storage/' . $generalSettings->logo) }}"
                             alt="{{ $generalSettings->site_name }}"
                             style="height: {{ $generalSettings->logo_height ?? '3rem' }}">
                    @endif
                    <span class="ml-2 font-bold" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                        {{ $generalSettings->site_name ?? 'Klubportal' }}
                    </span>
                </a>
            </div>

            {{-- Desktop Menu --}}
            <div class="hidden lg:flex gap-2 items-center">
                <a href="#features" class="btn btn-ghost">{{ __('nav.features') }}</a>
                <a href="#news" class="btn btn-ghost">{{ __('nav.news') }}</a>
                <a href="#pricing" class="btn btn-ghost">{{ __('nav.pricing') }}</a>
                <a href="#contact" class="btn btn-ghost">{{ __('nav.contact') }}</a>

                {{-- Language Switcher --}}
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 mt-4">
                        @php
                            $languages = [
                                'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪'],
                                'en' => ['name' => 'English', 'flag' => '🇬🇧'],
                                'hr' => ['name' => 'Hrvatski', 'flag' => '🇭🇷'],
                                'bs' => ['name' => 'Bosanski', 'flag' => '🇧🇦'],
                                'sr_Latn' => ['name' => 'Srpski', 'flag' => '🇷🇸'],
                                'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
                                'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
                                'it' => ['name' => 'Italiano', 'flag' => '🇮🇹'],
                                'pt' => ['name' => 'Português', 'flag' => '🇵🇹'],
                                'tr' => ['name' => 'Türkçe', 'flag' => '🇹🇷'],
                            ];
                            $currentLocale = app()->getLocale();
                        @endphp
                        @foreach($languages as $code => $lang)
                            <li>
                                <a href="{{ route('language.switch', $code) }}"
                                   class="flex items-center gap-2 {{ $currentLocale === $code ? 'active bg-primary text-white' : '' }}">
                                    <span class="text-xl">{{ $lang['flag'] }}</span>
                                    <span>{{ $lang['name'] }}</span>
                                    @if($currentLocale === $code)
                                        <span class="ml-auto">✓</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <a href="/super-admin/login" class="btn btn-ghost">{{ __('nav.login') }}</a>
                <a href="#register" class="btn" style="background-color: {{ $generalSettings->primary_color ?? '#dc2626' }}; color: white;">
                    {{ __('nav.register_club') }}
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <div class="lg:hidden">
                <button @click="mobileMenu = !mobileMenu" class="btn btn-ghost btn-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu" x-cloak class="lg:hidden">
            <div class="bg-base-200 p-4 space-y-2">
                <a href="#features" class="btn btn-ghost btn-block justify-start">{{ __('nav.features') }}</a>
                <a href="#news" class="btn btn-ghost btn-block justify-start">{{ __('nav.news') }}</a>
                <a href="#pricing" class="btn btn-ghost btn-block justify-start">{{ __('nav.pricing') }}</a>
                <a href="#contact" class="btn btn-ghost btn-block justify-start">{{ __('nav.contact') }}</a>

                {{-- Mobile Language Switcher --}}
                <details class="collapse collapse-arrow bg-base-100">
                    <summary class="collapse-title font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                        </svg>
                        {{ __('nav.language') }}
                    </summary>
                    <div class="collapse-content">
                        @php
                            $languages = [
                                'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪'],
                                'en' => ['name' => 'English', 'flag' => '🇬🇧'],
                                'hr' => ['name' => 'Hrvatski', 'flag' => '🇭🇷'],
                                'bs' => ['name' => 'Bosanski', 'flag' => '🇧🇦'],
                                'sr_Latn' => ['name' => 'Srpski', 'flag' => '🇷🇸'],
                                'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
                                'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
                                'it' => ['name' => 'Italiano', 'flag' => '🇮🇹'],
                                'pt' => ['name' => 'Português', 'flag' => '🇵🇹'],
                                'tr' => ['name' => 'Türkçe', 'flag' => '🇹🇷'],
                            ];
                            $currentLocale = app()->getLocale();
                        @endphp
                        @foreach($languages as $code => $lang)
                            <a href="{{ route('language.switch', $code) }}"
                               class="btn btn-ghost btn-sm btn-block justify-start gap-2 {{ $currentLocale === $code ? 'bg-primary text-white' : '' }}">
                                <span class="text-xl">{{ $lang['flag'] }}</span>
                                <span>{{ $lang['name'] }}</span>
                                @if($currentLocale === $code)
                                    <span class="ml-auto">✓</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </details>

                <a href="/super-admin/login" class="btn btn-ghost btn-block justify-start">{{ __('nav.login') }}</a>
                <a href="#register" class="btn btn-block" style="background-color: {{ $generalSettings->primary_color ?? '#dc2626' }}; color: white;">
                    {{ __('nav.register_club') }}
                </a>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative text-white overflow-hidden"
             style="background: linear-gradient(to bottom right, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="container mx-auto px-4 py-32 relative z-10">
            <div class="max-w-4xl mx-auto text-center animate-fadeInUp">
                <h1 class="text-5xl md:text-7xl font-bold mb-6">
                    {{ $generalSettings->site_name ?? 'Die moderne Vereinsverwaltung' }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">
                    {{ __('site.description') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#register" class="btn btn-lg bg-white text-primary hover:bg-gray-100 border-none">
                        {{ __('hero.start_free') }}
                    </a>
                    <a href="#features" class="btn btn-lg btn-outline text-white border-white hover:bg-white hover:text-primary">
                        {{ __('hero.learn_more') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Animated Background Elements --}}
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white to-transparent"></div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 bg-base-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 animate-fadeInUp">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Alles für deinen Verein</h2>
                <p class="text-xl text-gray-600">Professionelle Werkzeuge für moderne Vereinsarbeit</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="card bg-base-100 shadow-xl hover-lift">
                    <div class="card-body">
                        <div class="text-primary text-5xl mb-4">⚽</div>
                        <h3 class="card-title">Spielerverwaltung</h3>
                        <p>Verwalte Spieler, Teams und Kader übersichtlich. Mit Spielerstatistiken, Vertragsdaten und mehr.</p>
                    </div>
                </div>

                {{-- Feature 2 --}}
                <div class="card bg-base-100 shadow-xl hover-lift" style="animation-delay: 0.1s">
                    <div class="card-body">
                        <div class="text-primary text-5xl mb-4">📅</div>
                        <h3 class="card-title">Spielplanung</h3>
                        <p>Erstelle Spielpläne, verwalte Spielorte und teile Termine automatisch mit deinem Team.</p>
                    </div>
                </div>

                {{-- Feature 3 --}}
                <div class="card bg-base-100 shadow-xl hover-lift" style="animation-delay: 0.2s">
                    <div class="card-body">
                        <div class="text-primary text-5xl mb-4">🌐</div>
                        <h3 class="card-title">Eigene Website</h3>
                        <p>Moderne, responsive Website für deinen Verein. Automatisch mit News, Events und Spielergebnissen.</p>
                    </div>
                </div>

                {{-- Feature 4 --}}
                <div class="card bg-base-100 shadow-xl hover-lift" style="animation-delay: 0.3s">
                    <div class="card-body">
                        <div class="text-primary text-5xl mb-4">👥</div>
                        <h3 class="card-title">Mitgliederverwaltung</h3>
                        <p>Verwalte Mitglieder, Beiträge und Rollen einfach und übersichtlich.</p>
                    </div>
                </div>

                {{-- Feature 5 --}}
                <div class="card bg-base-100 shadow-xl hover-lift" style="animation-delay: 0.4s">
                    <div class="card-body">
                        <div class="text-primary text-5xl mb-4">📊</div>
                        <h3 class="card-title">Statistiken</h3>
                        <p>Detaillierte Statistiken über Spieler, Teams und Spiele. Immer den Überblick behalten.</p>
                    </div>
                </div>

                {{-- Feature 6 --}}
                <div class="card bg-base-100 shadow-xl hover-lift" style="animation-delay: 0.5s">
                    <div class="card-body">
                        <div class="text-primary text-5xl mb-4">📱</div>
                        <h3 class="card-title">Mobile App</h3>
                        <p>Nutze Klubportal überall - auf Desktop, Tablet und Smartphone.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- News Section --}}
    <section id="news" class="py-20 bg-base-200">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Neuigkeiten</h2>
                <p class="text-xl text-gray-600">Bleib auf dem Laufenden</p>
            </div>

            @if($latestNews->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($latestNews as $news)
                <div class="card bg-base-100 shadow-xl hover-lift">
                    @if($news->getFirstMediaUrl('featured_image'))
                    <figure>
                        <img src="{{ $news->getFirstMediaUrl('featured_image') }}" alt="{{ $news->title }}" class="w-full h-64 object-cover">
                    </figure>
                    @endif
                    <div class="card-body">
                        <div class="badge badge-primary mb-2">{{ $news->published_at->format('d.m.Y') }}</div>
                        <h3 class="card-title">{{ $news->title }}</h3>
                        <p>{{ Str::limit($news->excerpt ?? strip_tags($news->content), 120) }}</p>
                        <div class="card-actions justify-end mt-4">
                            <a href="#" class="btn btn-primary btn-sm">Weiterlesen</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center text-gray-500">
                <p class="text-xl">Keine Neuigkeiten verfügbar</p>
            </div>
            @endif
        </div>
    </section>

    {{-- Pricing Section --}}
    <section id="pricing" class="py-20 bg-base-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Transparente Preise</h2>
                <p class="text-xl text-gray-600">Wähle den Plan, der zu deinem Verein passt</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                {{-- Starter --}}
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-2xl">Starter</h3>
                        <div class="my-4">
                            <span class="text-5xl font-bold">0€</span>
                            <span class="text-gray-600">/Monat</span>
                        </div>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> Bis 50 Mitglieder
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> 2 Teams
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> Basis-Website
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> E-Mail Support
                            </li>
                        </ul>
                        <a href="#register" class="btn btn-outline btn-primary">Jetzt starten</a>
                    </div>
                </div>

                {{-- Professional --}}
                <div class="card bg-primary text-white shadow-2xl scale-105">
                    <div class="card-body">
                        <div class="badge badge-secondary mb-2">Beliebt</div>
                        <h3 class="card-title text-2xl">Professional</h3>
                        <div class="my-4">
                            <span class="text-5xl font-bold">29€</span>
                            <span class="opacity-80">/Monat</span>
                        </div>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-yellow-300">✓</span> Bis 200 Mitglieder
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-yellow-300">✓</span> Unbegrenzte Teams
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-yellow-300">✓</span> Premium-Website
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-yellow-300">✓</span> Prioritäts-Support
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-yellow-300">✓</span> Statistiken & Reports
                            </li>
                        </ul>
                        <a href="#register" class="btn btn-secondary">Jetzt starten</a>
                    </div>
                </div>

                {{-- Enterprise --}}
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-2xl">Enterprise</h3>
                        <div class="my-4">
                            <span class="text-5xl font-bold">79€</span>
                            <span class="text-gray-600">/Monat</span>
                        </div>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> Unbegrenzte Mitglieder
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> Unbegrenzte Teams
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> Custom Website
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> Dedizierter Support
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> API Zugang
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-green-500">✓</span> White Label
                            </li>
                        </ul>
                        <a href="#register" class="btn btn-outline btn-primary">Kontakt aufnehmen</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Register Section --}}
    <section id="register" class="py-20 bg-gradient-to-br from-primary to-red-700 text-white">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold mb-4">Verein registrieren</h2>
                    <p class="text-xl opacity-90">Starte noch heute - kostenlos und unverbindlich</p>
                </div>

                @if($register_success)
                <div class="alert alert-success mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Vielen Dank! Wir melden uns in Kürze bei Ihnen.</span>
                </div>
                @endif

                <form wire:submit.prevent="submitRegistration" class="card bg-white text-gray-800 shadow-2xl">
                    <div class="card-body">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Vereinsname *</span>
                            </label>
                            <input type="text" wire:model="register_club_name" class="input input-bordered" placeholder="FC Musterstadt" required>
                            @error('register_club_name') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">E-Mail Adresse *</span>
                            </label>
                            <input type="email" wire:model="register_email" class="input input-bordered" placeholder="info@verein.de" required>
                            @error('register_email') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Telefon *</span>
                            </label>
                            <input type="tel" wire:model="register_phone" class="input input-bordered" placeholder="+49 123 456789" required>
                            @error('register_phone') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Nachricht (optional)</span>
                            </label>
                            <textarea wire:model="register_message" class="textarea textarea-bordered" rows="4" placeholder="Erzählen Sie uns mehr über Ihren Verein..."></textarea>
                        </div>

                        <div class="form-control mt-6">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <span wire:loading.remove>Registrierung starten</span>
                                <span wire:loading>Wird gesendet...</span>
                            </button>
                        </div>

                        <p class="text-sm text-gray-600 text-center mt-4">
                            * Pflichtfelder. Ihre Daten werden vertraulich behandelt.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Contact Section --}}
    <section id="contact" class="py-20 bg-base-200">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold mb-4">Kontakt</h2>
                    <p class="text-xl text-gray-600">Haben Sie Fragen? Wir helfen gerne!</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Contact Form --}}
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Nachricht senden</h3>

                            @if($contact_success)
                            <div class="alert alert-success mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Nachricht erfolgreich gesendet!</span>
                            </div>
                            @endif

                            <form wire:submit.prevent="submitContact">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">Name</span>
                                    </label>
                                    <input type="text" wire:model="contact_name" class="input input-bordered" required>
                                    @error('contact_name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">E-Mail</span>
                                    </label>
                                    <input type="email" wire:model="contact_email" class="input input-bordered" required>
                                    @error('contact_email') <span class="text-error text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">Telefon (optional)</span>
                                    </label>
                                    <input type="tel" wire:model="contact_phone" class="input input-bordered">
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">Nachricht</span>
                                    </label>
                                    <textarea wire:model="contact_message" class="textarea textarea-bordered" rows="4" required></textarea>
                                    @error('contact_message') <span class="text-error text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-control mt-6">
                                    <button type="submit" class="btn text-white" style="background-color: {{ $generalSettings->primary_color ?? '#dc2626' }};">
                                        <span wire:loading.remove>Absenden</span>
                                        <span wire:loading>Wird gesendet...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="space-y-6">
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <h3 class="card-title" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }};">📧 E-Mail</h3>
                                <p>{{ $contactSettings->email ?? $generalSettings->contact_email ?? 'info@klubportal.com' }}</p>
                                <p class="text-sm text-gray-600">Wir antworten innerhalb von 24 Stunden</p>
                            </div>
                        </div>

                        @if($contactSettings->phone || $generalSettings->phone)
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <h3 class="card-title" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }};">📞 Telefon</h3>
                                <p>{{ $contactSettings->phone ?? $generalSettings->phone }}</p>
                                <p class="text-sm text-gray-600">Mo-Fr: 9:00 - 17:00 Uhr</p>
                            </div>
                        </div>
                        @endif

                        @if($contactSettings->street && $contactSettings->city)
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <h3 class="card-title" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }};">📍 Adresse</h3>
                                <p>{{ $contactSettings->company_name ?? $generalSettings->site_name }}</p>
                                @if($contactSettings->street)
                                    <p>{{ $contactSettings->street }}</p>
                                @endif
                                @if($contactSettings->postal_code || $contactSettings->city)
                                    <p>{{ $contactSettings->postal_code }} {{ $contactSettings->city }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="text-white mt-20" style="background-color: {{ $themeSettings->footer_bg_color ?? '#1f2937' }};">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- About --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Über {{ $generalSettings->site_name ?? 'Klubportal' }}</h3>
                    <p class="text-sm opacity-80">
                        {{ $generalSettings->site_description ?? 'Die moderne All-in-One Lösung für Vereinsverwaltung, Spielerverwaltung und Öffentlichkeitsarbeit.' }}
                    </p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm opacity-80">
                        <li><a href="#features" class="hover:opacity-100" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">Features</a></li>
                        <li><a href="#pricing" class="hover:opacity-100" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">Preise</a></li>
                        <li><a href="#news" class="hover:opacity-100" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">News</a></li>
                        <li><a href="#contact" class="hover:opacity-100" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">Kontakt</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Rechtliches</h3>
                    <ul class="space-y-2 text-sm opacity-80">
                        <li><a href="#" class="hover:opacity-100">Impressum</a></li>
                        <li><a href="#" class="hover:opacity-100">Datenschutz</a></li>
                        <li><a href="#" class="hover:opacity-100">AGB</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Kontakt</h3>
                    <ul class="space-y-2 text-sm opacity-80">
                        @if($contactSettings->company_name)
                            <li>{{ $contactSettings->company_name }}</li>
                        @endif
                        @if($contactSettings->email)
                            <li>E-Mail: {{ $contactSettings->email }}</li>
                        @else
                            <li>E-Mail: {{ $generalSettings->contact_email ?? 'info@klubportal.com' }}</li>
                        @endif
                        @if($contactSettings->phone)
                            <li>Tel: {{ $contactSettings->phone }}</li>
                        @endif
                        @if($contactSettings->mobile)
                            <li>Mobil: {{ $contactSettings->mobile }}</li>
                        @endif

                        {{-- Social Media Links --}}
                        @if($socialSettings->facebook_url || $socialSettings->instagram_url || $socialSettings->twitter_url || $socialSettings->youtube_url)
                            <li class="pt-2">
                                <div class="flex gap-3">
                                    @if($socialSettings->facebook_url)
                                        <a href="{{ $socialSettings->facebook_url }}" target="_blank" class="hover:opacity-100" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        </a>
                                    @endif
                                    @if($socialSettings->instagram_url)
                                        <a href="{{ $socialSettings->instagram_url }}" target="_blank" class="hover:opacity-100" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                        </a>
                                    @endif
                                    @if($socialSettings->twitter_url)
                                        <a href="{{ $socialSettings->twitter_url }}" target="_blank" class="hover:opacity-100" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                        </a>
                                    @endif
                                    @if($socialSettings->youtube_url)
                                        <a href="{{ $socialSettings->youtube_url }}" target="_blank" class="hover:opacity-100" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="border-t border-white/20 mt-8 pt-8 text-center text-sm opacity-60">
                <p>&copy; {{ date('Y') }} {{ $generalSettings->site_name ?? 'Klubportal' }}. Alle Rechte vorbehalten.</p>
            </div>
        </div>
    </footer>

    {{-- Back to Top Button --}}
    <button
        onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="btn btn-circle fixed bottom-8 right-8 shadow-lg text-white"
        style="background-color: {{ $generalSettings->primary_color ?? '#dc2626' }};"
        x-data="{ show: false }"
        @scroll.window="show = window.pageYOffset > 300"
        x-show="show"
        x-cloak
    >
        ↑
    </button>
</div>
