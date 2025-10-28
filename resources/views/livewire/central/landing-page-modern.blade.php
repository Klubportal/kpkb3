<div x-data="{
        mobileMenu: false,
        activeFeature: 0,
        scrolled: false
     }"
     @scroll.window="scrolled = window.pageYOffset > 50"
     class="min-h-screen">

    @section('title', $generalSettings->site_name ?? 'Klubportal - Moderne Vereinsverwaltung')
    @section('favicon', $generalSettings->favicon ? (str_starts_with($generalSettings->favicon, 'http') ? $generalSettings->favicon : Storage::url($generalSettings->favicon)) : asset('favicon.ico'))

    {{-- Ultra-Modern Navigation --}}
    <nav class="fixed w-full top-0 z-50 transition-all duration-500"
         :class="scrolled ? 'glass-card border-b border-gray-200/50 py-3' : 'bg-transparent py-5'">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between">
                {{-- Logo with Animation --}}
                <a href="/" class="group flex items-center space-x-3 relative">
                    @if($generalSettings->logo)
                        <div class="relative overflow-hidden rounded-xl">
                            <img src="{{ asset('storage/' . $generalSettings->logo) }}"
                                 alt="{{ $generalSettings->site_name }}"
                                 style="height: {{ $generalSettings->logo_height ?? '2.5rem' }}"
                                 class="drop-shadow-2xl transform group-hover:scale-110 transition-transform duration-300">
                            <div class="absolute inset-0 shimmer group-hover:opacity-50"></div>
                        </div>
                    @endif
                    <span class="text-2xl font-extrabold bg-gradient-to-r from-red-600 via-pink-600 to-purple-600 bg-clip-text text-transparent group-hover:from-purple-600 group-hover:via-pink-600 group-hover:to-red-600 transition-all duration-500"
                          :class="!scrolled && 'text-white drop-shadow-lg'">
                        {{ $generalSettings->site_name ?? 'Klubportal' }}
                    </span>
                </a>

                {{-- Desktop Menu with Modern Styling --}}
                <div class="hidden lg:flex items-center space-x-2">
                    <a href="#features"
                       class="relative px-4 py-2.5 font-medium rounded-xl transition-all duration-300 group overflow-hidden"
                       :class="scrolled ? 'hover:bg-gray-100' : 'hover:bg-white/10 text-white'">
                        <span class="relative z-10">{{ __('nav.features') }}</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-pink-500/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                    </a>
                    <a href="#news"
                       class="relative px-4 py-2.5 font-medium rounded-xl transition-all duration-300 group overflow-hidden"
                       :class="scrolled ? 'hover:bg-gray-100' : 'hover:bg-white/10 text-white'">
                        <span class="relative z-10">{{ __('nav.news') }}</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-pink-500/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                    </a>
                    <a href="#contact"
                       class="relative px-4 py-2.5 font-medium rounded-xl transition-all duration-300 group overflow-hidden"
                       :class="scrolled ? 'hover:bg-gray-100' : 'hover:bg-white/10 text-white'">
                        <span class="relative z-10">{{ __('nav.contact') }}</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-pink-500/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                    </a>

                    {{-- Language Switcher --}}
                    <div class="dropdown dropdown-end" :class="scrolled ? '' : 'text-white'">
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

                    {{-- CTA Button - Modern & Prominent --}}
                    <a href="/register"
                       class="relative ml-3 px-8 py-4 rounded-2xl text-white font-bold text-lg shadow-2xl hover-scale group overflow-hidden"
                       style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                        <span class="relative z-10 flex items-center justify-center space-x-2.5">
                            <span>{{ __('nav.register_club') }}</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                        <div class="absolute inset-0 bg-white/20 translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
                    </a>
                </div>

                {{-- Modern Mobile Menu Button --}}
                <button @click="mobileMenu = !mobileMenu"
                        class="lg:hidden relative p-2.5 rounded-xl transition-all duration-300"
                        :class="scrolled ? 'hover:bg-gray-100' : 'hover:bg-white/10'">
                    <svg class="w-6 h-6 transition-transform duration-300"
                         :class="mobileMenu && 'rotate-90'"
                         :style="!scrolled && 'color: white'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              x-show="!mobileMenu"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              x-show="mobileMenu"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modern Mobile Menu --}}
            <div x-show="mobileMenu"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="lg:hidden mt-6 space-y-2 pb-4">
                <a href="#features"
                   @click="mobileMenu = false"
                   class="block px-5 py-3.5 rounded-xl font-medium transition-all duration-300"
                   :class="scrolled ? 'hover:bg-gray-100' : 'hover:bg-white/10 text-white'">
                    <div class="flex items-center space-x-3">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $generalSettings->primary_color ?? '#dc2626' }}"></span>
                        <span>{{ __('Features') }}</span>
                    </div>
                </a>
                <a href="#news"
                   @click="mobileMenu = false"
                   class="block px-5 py-3.5 rounded-xl font-medium transition-all duration-300"
                   :class="scrolled ? 'hover:bg-gray-100' : 'hover:bg-white/10 text-white'">
                    <div class="flex items-center space-x-3">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $generalSettings->primary_color ?? '#dc2626' }}"></span>
                        <span>{{ __('News') }}</span>
                    </div>
                </a>
                <a href="#pricing"
                   @click="mobileMenu = false"
                   class="block px-5 py-3.5 rounded-xl font-medium transition-all duration-300"
                   :class="scrolled ? 'hover:bg-gray-100' : 'hover:bg-white/10 text-white'">
                    <div class="flex items-center space-x-3">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $generalSettings->primary_color ?? '#dc2626' }}"></span>
                        <span>{{ __('Pricing') }}</span>
                    </div>
                </a>
                <a href="#contact"
                   @click="mobileMenu = false"
                   class="block px-5 py-3.5 rounded-xl font-medium transition-all duration-300"
                   :class="scrolled ? 'hover:bg-gray-100' : 'hover:bg-white/10 text-white'">
                    <div class="flex items-center space-x-3">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $generalSettings->primary_color ?? '#dc2626' }}"></span>
                        <span>{{ __('Contact') }}</span>
                    </div>
                </a>
                <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent my-4"></div>
                <a href="/register"
                   @click="mobileMenu = false"
                   class="block px-6 py-4 rounded-2xl text-center text-white font-bold text-lg shadow-xl"
                   style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                    {{ __('Register Club') }}
                </a>
            </div>
        </div>
    </nav>

    {{-- Hero Section with Animated Gradient --}}
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
        {{-- Animated Background --}}
        <div class="absolute inset-0 gradient-animation opacity-90"></div>

        {{-- Floating Shapes --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-float"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
        </div>

        {{-- Hero Content --}}
        <div class="container mx-auto px-4 py-32 relative z-10">
            <div class="max-w-5xl mx-auto text-center">
                <h1 class="text-6xl md:text-8xl font-extrabold text-white mb-8 animate-fadeInUp drop-shadow-2xl">
                    {{ $generalSettings->site_name ?? __('Your Football Club Management Platform') }}
                </h1>
                <p class="text-xl md:text-3xl text-white/90 mb-12 animate-fadeInUp leading-relaxed" style="animation-delay: 0.2s;">
                    {{ __('site.description') }}
                </p>

                <div class="flex flex-col sm:flex-row gap-6 justify-center animate-fadeInUp" style="animation-delay: 0.4s;">
                    <a href="#register" class="group px-8 py-4 bg-white text-gray-900 rounded-full font-bold text-lg shadow-2xl hover-scale inline-flex items-center justify-center space-x-2">
                        <span>{{ __('hero.start_free') }}</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#features" class="px-8 py-4 glass text-white rounded-full font-bold text-lg hover-scale border-2 border-white/30">
                        {{ __('hero.learn_more') }}
                    </a>
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-8 mt-20 animate-fadeInUp" style="animation-delay: 0.6s;">
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="text-4xl font-bold" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">500+</div>
                        <div class="text-gray-600 mt-2">{{ __('Active Clubs') }}</div>
                    </div>
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="text-4xl font-bold" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">100k+</div>
                        <div class="text-gray-600 mt-2">{{ __('Managed Players') }}</div>
                    </div>
                    <div class="glass-card rounded-2xl p-6 hover-lift">
                        <div class="text-4xl font-bold" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">99%</div>
                        <div class="text-gray-600 mt-2">{{ __('Satisfaction') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scroll Indicator --}}
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 md:py-28 lg:py-32 bg-white relative overflow-hidden">
        {{-- Animated Background Decoration --}}
        <div class="absolute inset-0 opacity-5">
            <div class="absolute top-20 left-10 w-72 h-72 bg-red-500 rounded-full blur-3xl animate-float"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-pink-500 rounded-full blur-3xl animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-purple-400 rounded-full blur-3xl animate-float" style="animation-delay: 4s;"></div>
        </div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            {{-- Header mit 20px spacing --}}
            <div class="text-center mb-20 reveal" style="padding-bottom: 20px;">
                <div class="inline-flex items-center justify-center mb-6">
                    <div class="h-px w-12 bg-gradient-to-r from-transparent to-red-500 mr-3"></div>
                    <span class="inline-block px-6 py-2.5 rounded-full text-sm font-bold uppercase tracking-wider"
                          style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}15, {{ $generalSettings->secondary_color ?? '#991b1b' }}15); color: {{ $generalSettings->primary_color ?? '#dc2626' }}; box-shadow: 0 0 20px {{ $generalSettings->primary_color ?? '#dc2626' }}20;">
                        {{ __('Features') }}
                    </span>
                    <div class="h-px w-12 bg-gradient-to-l from-transparent to-red-500 ml-3"></div>
                </div>

                <h2 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold mb-6 bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 bg-clip-text text-transparent leading-tight" style="margin-top: 20px; margin-bottom: 20px;">
                    {{ __('Everything You Need') }}
                </h2>

                <p class="text-lg sm:text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto leading-relaxed px-4" style="margin-top: 20px;">
                    {{ __('Professional Tools for Modern Club Management - Everything in One Platform') }}
                </p>

                {{-- Animated Divider --}}
                <div class="flex justify-center mt-8">
                    <div class="w-24 h-1.5 rounded-full overflow-hidden" style="background: linear-gradient(90deg, transparent, {{ $generalSettings->primary_color ?? '#dc2626' }}, transparent);">
                        <div class="h-full w-1/2 bg-white shimmer"></div>
                    </div>
                </div>
            </div>

            {{-- Responsive Grid mit optimierten Abständen --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 lg:gap-10">
                {{-- Feature Cards --}}
                @php
                $features = [
                    [
                        'icon' => '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>',
                        'title' => __('Club Website'),
                        'desc' => __('Modern, responsive website with CMS - no programming knowledge required'),
                        'badge' => __('Popular')
                    ],
                    [
                        'icon' => '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                        'title' => __('Mobile App'),
                        'desc' => __('All games from all teams automatically - Create games & live score'),
                        'badge' => __('New')
                    ],
                    [
                        'icon' => '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                        'title' => __('Player Management'),
                        'desc' => __('Complete player database with statistics, contracts and development tracking'),
                        'badge' => null
                    ],
                    [
                        'icon' => '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                        'title' => __('Game Planning'),
                        'desc' => __('Intelligent calendar integration with automatic notifications'),
                        'badge' => null
                    ],
                    [
                        'icon' => '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                        'title' => __('Analytics'),
                        'desc' => __('Detailed statistics and reports for better decisions'),
                        'badge' => null
                    ],
                    [
                        'icon' => '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                        'title' => __('Financial Management'),
                        'desc' => __('Keep track of membership fees, donations and expenses'),
                        'badge' => __('Pro')
                    ],
                ];
                @endphp

                @foreach($features as $index => $feature)
                <div class="reveal group h-full" style="animation-delay: {{ $index * 0.08 }}s;">
                    <div class="relative h-full glass-card rounded-3xl p-8 md:p-10 hover-lift transition-all duration-700 border border-gray-100 hover:border-transparent hover:shadow-2xl overflow-hidden">

                        {{-- Badge - GANZ OBEN mit höchstem z-index --}}
                        @if($feature['badge'])
                        <div class="absolute top-4 right-4 z-50">
                            <span class="px-4 py-1.5 text-sm font-bold rounded-full text-white shadow-lg"
                                  style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                                {{ $feature['badge'] }}
                            </span>
                        </div>
                        @endif

                        {{-- Animated Gradient Border --}}
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                            <div class="absolute inset-0 rounded-3xl p-[2px]" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }}); opacity: 0.3;"></div>
                        </div>

                        {{-- Gradient Background on Hover --}}
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-5 transition-opacity duration-700"
                             style="background: linear-gradient(135deg, #000000, #ff0000)"></div>

                        {{-- Icon Container mit 20px margin - LINKS positioniert --}}
                        <div class="relative mb-6 flex justify-start z-10" style="margin-bottom: 20px;">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-700 shadow-xl"
                                 style="background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 group-hover:scale-110 transition-transform duration-700 text-white">
                                    {!! $feature['icon'] !!}
                                </div>
                            </div>
                        </div>

                        {{-- Content mit 20px spacing --}}
                        <div class="relative z-10">
                            <h3 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4 group-hover:translate-x-1 transition-transform duration-500" style="margin-bottom: 20px;">
                                <span class="bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent group-hover:from-red-600 group-hover:to-pink-600 transition-all duration-500">
                                    {{ $feature['title'] }}
                                </span>
                            </h3>

                            <p class="text-sm sm:text-base text-gray-600 leading-relaxed mb-6" style="margin-bottom: 20px;">
                                {{ $feature['desc'] }}
                            </p>

                            {{-- Action Link --}}
                            <div class="flex items-center font-semibold text-sm group-hover:translate-x-2 transition-all duration-500"
                                 style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}; margin-top: 20px;">
                                <span>{{ __('Learn More') }}</span>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-2 group-hover:translate-x-2 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </div>

                            {{-- Progress Bar Animation on Hover --}}
                            <div class="mt-6 h-1 w-0 group-hover:w-full transition-all duration-1000 rounded-full" style="background: linear-gradient(90deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Call to Action unter Features --}}
            <div class="text-center mt-20 reveal" style="margin-top: 20px; padding-top: 20px;">
                <a href="#register" class="inline-flex items-center px-8 py-4 rounded-full text-white font-bold text-lg shadow-2xl hover-scale group"
                   style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                    <span>{{ __('Discover All Features') }}</span>
                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- News Section --}}
    @if($latestNews && $latestNews->count() > 0)
    <section id="news" class="py-32 bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-20 reveal">
                <span class="text-sm font-semibold uppercase tracking-wider" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                    {{ __('Latest News') }}
                </span>
                <h2 class="text-5xl md:text-6xl font-bold mt-4 mb-6">{{ __('News & Updates') }}</h2>
                <p class="text-xl text-gray-600">{{ __('Stay informed about new features and developments') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($latestNews as $index => $article)
                <article class="reveal glass-card rounded-2xl overflow-hidden hover-lift group" style="animation-delay: {{ $index * 0.15 }}s;">
                    @if($article->getFirstMediaUrl('featured'))
                    <div class="relative overflow-hidden h-64">
                        <img src="{{ $article->getFirstMediaUrl('featured') }}"
                             alt="{{ $article->title }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    </div>
                    @endif

                    <div class="p-8">
                        <div class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <time>{{ $article->published_at->format('d.m.Y') }}</time>
                        </div>

                        <h3 class="text-2xl font-bold mb-4 group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-red-600 group-hover:to-pink-600 group-hover:bg-clip-text transition-all">
                            {{ $article->title }}
                        </h3>

                        <p class="text-gray-600 mb-6 line-clamp-3">
                            {{ Str::limit(strip_tags($article->content), 120) }}
                        </p>

                        <a href="/news/{{ $article->slug }}" class="inline-flex items-center font-semibold group-hover:translate-x-2 transition-transform" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                            {{ __('Read More') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Pricing Section --}}
    <section id="pricing" class="py-32 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-20 reveal">
                <span class="text-sm font-semibold uppercase tracking-wider" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                    {{ __('Pricing') }}
                </span>
                <h2 class="text-5xl md:text-6xl font-bold mt-4 mb-6">{{ __('Fair & Transparent') }}</h2>
                <p class="text-xl text-gray-600">{{ __('Choose the package that fits your club') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                {{-- Basic --}}
                <div class="reveal glass-card rounded-3xl p-8 hover-lift">
                    <h3 class="text-2xl font-bold mb-2">{{ __('Basic') }}</h3>
                    <div class="text-5xl font-bold my-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                        {{ __('Free') }}
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Up to 50 players') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Basic Website') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Game Planning') }}</span>
                        </li>
                    </ul>
                    <a href="#register" class="block w-full text-center px-6 py-3 rounded-full font-semibold border-2 hover-scale transition-all"
                       style="border-color: {{ $generalSettings->primary_color ?? '#dc2626' }}; color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                        {{ __('Start') }}
                    </a>
                </div>

                {{-- Pro (Featured) --}}
                <div class="reveal glass-card rounded-3xl p-8 hover-lift relative" style="animation-delay: 0.1s; background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}15, {{ $generalSettings->secondary_color ?? '#991b1b' }}15); border: 2px solid {{ $generalSettings->primary_color ?? '#dc2626' }};">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 px-4 py-1 rounded-full text-white text-sm font-semibold"
                         style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                        {{ __('Popular') }}
                    </div>
                    <h3 class="text-2xl font-bold mb-2">{{ __('Pro') }}</h3>
                    <div class="text-5xl font-bold my-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                        €29<span class="text-xl">{{ __('per month') }}</span>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>{{ __('Unlimited Players') }}</strong></span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Premium Website Templates') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Analytics & Reports') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Priority Support') }}</span>
                        </li>
                    </ul>
                    <a href="#register" class="block w-full text-center px-6 py-3 rounded-full font-semibold text-white hover-scale shadow-lg"
                       style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                        {{ __('Start Now') }}
                    </a>
                </div>

                {{-- Enterprise --}}
                <div class="reveal glass-card rounded-3xl p-8 hover-lift" style="animation-delay: 0.2s;">
                    <h3 class="text-2xl font-bold mb-2">{{ __('Enterprise') }}</h3>
                    <div class="text-5xl font-bold my-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                        {{ __('Individual') }}
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Everything from Pro') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Custom Development') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('API Access') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-2 flex-shrink-0" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Dedicated Support') }}</span>
                        </li>
                    </ul>
                    <a href="#contact" class="block w-full text-center px-6 py-3 rounded-full font-semibold border-2 hover-scale transition-all"
                       style="border-color: {{ $generalSettings->primary_color ?? '#dc2626' }}; color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                        {{ __('Contact') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Registration Section --}}
    <section id="register" class="py-32 bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-16 reveal">
                    <span class="text-sm font-semibold uppercase tracking-wider" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                        {{ __('Registration') }}
                    </span>
                    <h2 class="text-5xl md:text-6xl font-bold mt-4 mb-6">{{ __('Start Now') }}</h2>
                    <p class="text-xl text-gray-600">{{ __('Get your own club platform in minutes') }}</p>
                </div>

                <div class="glass-card rounded-3xl p-10 reveal">
                    @if($register_success)
                    <div class="p-6 bg-green-50 border-2 border-green-500 rounded-2xl text-center">
                        <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-2xl font-bold text-green-800 mb-2">{{ __('Thank you!') }}</h3>
                        <p class="text-green-700">{{ __('We have received your request and will contact you shortly.') }}</p>
                    </div>
                    @else
                    <form wire:submit.prevent="submitRegistration" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2">{{ __('Club Name') }} *</label>
                                <input type="text" wire:model="register_club_name"
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all"
                                       required>
                                @error('register_club_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">{{ __('Email') }} *</label>
                                <input type="email" wire:model="register_email"
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all"
                                       required>
                                @error('register_email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">{{ __('Phone') }} *</label>
                            <input type="tel" wire:model="register_phone"
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all"
                                   required>
                            @error('register_phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">{{ __('Message (optional)') }}</label>
                            <textarea wire:model="register_message" rows="4"
                                      class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all"></textarea>
                        </div>
                        <button type="submit" class="w-full px-8 py-4 rounded-full text-white font-bold text-lg hover-scale shadow-xl"
                                style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                            <span wire:loading.remove>{{ __('Register Now') }}</span>
                            <span wire:loading>{{ __('Sending...') }}</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Contact Section --}}
    <section id="contact" class="py-32 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 reveal">
                <span class="text-sm font-semibold uppercase tracking-wider" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}">
                    {{ __('Contact') }}
                </span>
                <h2 class="text-5xl md:text-6xl font-bold mt-4 mb-6">{{ __('We are here for you') }}</h2>
                <p class="text-xl text-gray-600">{{ __('Questions? Send us a message') }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-6xl mx-auto">
                {{-- Contact Form --}}
                <div class="reveal glass-card rounded-3xl p-10">
                    @if($contact_success)
                    <div class="p-6 bg-green-50 border-2 border-green-500 rounded-2xl text-center">
                        <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-2xl font-bold text-green-800 mb-2">{{ __('Message sent!') }}</h3>
                        <p class="text-green-700">{{ __('We will contact you as soon as possible.') }}</p>
                    </div>
                    @else
                    <form wire:submit.prevent="submitContact" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold mb-2">{{ __('Name') }} *</label>
                            <input type="text" wire:model="contact_name"
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all"
                                   required>
                            @error('contact_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">{{ __('Email') }} *</label>
                            <input type="email" wire:model="contact_email"
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all"
                                   required>
                            @error('contact_email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">{{ __('Phone') }} ({{ __('Message (optional)') }})</label>
                            <input type="tel" wire:model="contact_phone"
                                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">{{ __('Message') }} *</label>
                            <textarea wire:model="contact_message" rows="5"
                                      class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all"
                                      required></textarea>
                            @error('contact_message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="w-full px-8 py-4 rounded-full text-white font-bold text-lg hover-scale shadow-xl"
                                style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                            <span wire:loading.remove>{{ __('Send Message') }}</span>
                            <span wire:loading>{{ __('Sending...') }}</span>
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Contact Info --}}
                <div class="space-y-6 reveal" style="animation-delay: 0.2s;">
                    @if($contactSettings->email || $generalSettings->contact_email)
                    <div class="glass-card rounded-2xl p-8 hover-lift group">
                        <div class="flex items-start space-x-4">
                            <div class="p-3 rounded-xl group-hover:scale-110 transition-transform" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}20, {{ $generalSettings->secondary_color ?? '#991b1b' }}20);">
                                <svg class="w-6 h-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl mb-2">{{ __('Email') }}</h3>
                                <p class="text-gray-600">{{ $contactSettings->email ?? $generalSettings->contact_email }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Response within 24 hours') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($contactSettings->phone || $generalSettings->phone)
                    <div class="glass-card rounded-2xl p-8 hover-lift group">
                        <div class="flex items-start space-x-4">
                            <div class="p-3 rounded-xl group-hover:scale-110 transition-transform" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}20, {{ $generalSettings->secondary_color ?? '#991b1b' }}20);">
                                <svg class="w-6 h-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl mb-2">{{ __('Phone') }}</h3>
                                <p class="text-gray-600">{{ $contactSettings->phone ?? $generalSettings->phone }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Monday-Friday: 9:00 - 17:00') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($contactSettings->street && $contactSettings->city)
                    <div class="glass-card rounded-2xl p-8 hover-lift group">
                        <div class="flex items-start space-x-4">
                            <div class="p-3 rounded-xl group-hover:scale-110 transition-transform" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}20, {{ $generalSettings->secondary_color ?? '#991b1b' }}20);">
                                <svg class="w-6 h-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl mb-2">{{ __('Address') }}</h3>
                                <p class="text-gray-600">{{ $contactSettings->company_name ?? $generalSettings->site_name }}</p>
                                <p class="text-gray-600">{{ $contactSettings->street }}</p>
                                <p class="text-gray-600">{{ $contactSettings->postal_code }} {{ $contactSettings->city }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($socialSettings->facebook_url || $socialSettings->instagram_url || $socialSettings->twitter_url || $socialSettings->youtube_url)
                    <div class="glass-card rounded-2xl p-8">
                        <h3 class="font-bold text-xl mb-4">{{ __('Follow us') }}</h3>
                        <div class="flex space-x-4">
                            @if($socialSettings->facebook_url)
                            <a href="{{ $socialSettings->facebook_url }}" target="_blank" class="p-3 rounded-xl hover-scale transition-all" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}20, {{ $generalSettings->secondary_color ?? '#991b1b' }}20);">
                                <svg class="w-6 h-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            @endif
                            @if($socialSettings->instagram_url)
                            <a href="{{ $socialSettings->instagram_url }}" target="_blank" class="p-3 rounded-xl hover-scale transition-all" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}20, {{ $generalSettings->secondary_color ?? '#991b1b' }}20);">
                                <svg class="w-6 h-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                            @endif
                            @if($socialSettings->twitter_url)
                            <a href="{{ $socialSettings->twitter_url }}" target="_blank" class="p-3 rounded-xl hover-scale transition-all" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}20, {{ $generalSettings->secondary_color ?? '#991b1b' }}20);">
                                <svg class="w-6 h-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                            @endif
                            @if($socialSettings->youtube_url)
                            <a href="{{ $socialSettings->youtube_url }}" target="_blank" class="p-3 rounded-xl hover-scale transition-all" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}20, {{ $generalSettings->secondary_color ?? '#991b1b' }}20);">
                                <svg class="w-6 h-6" style="color: {{ $generalSettings->primary_color ?? '#dc2626' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Modern Footer --}}
    <footer class="text-white py-16" style="background: linear-gradient(135deg, {{ $themeSettings->footer_bg_color ?? '#1f2937' }}, #000);">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div>
                    <h3 class="text-2xl font-bold mb-4 bg-gradient-to-r from-red-400 to-pink-400 bg-clip-text text-transparent">
                        {{ $generalSettings->site_name ?? 'Klubportal' }}
                    </h3>
                    <p class="text-gray-400 leading-relaxed">
                        {{ __('site.description') }}
                    </p>
                </div>

                <div>
                    <h4 class="font-bold mb-4 text-lg">{{ __('Quick Links') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-white transition-colors">{{ __('nav.features') }}</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">{{ __('nav.pricing') }}</a></li>
                        <li><a href="#news" class="hover:text-white transition-colors">{{ __('nav.news') }}</a></li>
                        <li><a href="#contact" class="hover:text-white transition-colors">{{ __('nav.contact') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4 text-lg">{{ __('Legal') }}</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Imprint') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Privacy Policy') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('Terms & Conditions') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4 text-lg">{{ __('Newsletter') }}</h4>
                    <p class="text-gray-400 mb-4">{{ __('Stay up to date') }}</p>
                    <form class="flex">
                        <input type="email" placeholder="{{ __('Your Email') }}" class="flex-1 px-4 py-2 rounded-l-lg bg-white/10 border border-white/20 focus:outline-none focus:border-red-500">
                        <button type="submit" class="px-6 py-2 rounded-r-lg font-semibold" style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
                            →
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} {{ $generalSettings->site_name ?? 'Klubportal' }}. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </footer>

    {{-- Back to Top Button --}}
    <button @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            x-data="{ show: false }"
            @scroll.window="show = window.pageYOffset > 500"
            x-show="show"
            x-transition
            class="fixed bottom-8 right-8 p-4 rounded-full text-white shadow-2xl hover-scale z-40"
            style="background: linear-gradient(135deg, {{ $generalSettings->primary_color ?? '#dc2626' }}, {{ $generalSettings->secondary_color ?? '#991b1b' }});">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>
</div>
