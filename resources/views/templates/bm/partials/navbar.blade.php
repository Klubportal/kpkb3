<nav class="header-bg dark:bg-gray-900 shadow-xl sticky top-0 z-50 border-b-4 border-primary" x-data="{ scrolled: false }" @scroll.window="scrolled = window.pageYOffset > 20">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-20">
            <div class="flex items-center space-x-4">
                @if($settings->logo)
                <img src="/storage/{{ $settings->logo }}" alt="Logo" class="h-14 w-14 object-contain">
                @else
                <div class="w-14 h-14 badge-bg rounded-full flex items-center justify-center shadow-lg">
                    <svg class="w-9 h-9 badge-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold header-text">{{ $settings->website_name ?? 'Verein' }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $settings->slogan ?? 'Mia San Mia' }}</p>
                </div>
            </div>

            <div class="hidden lg:flex items-center space-x-8">
                <a href="/" class="group relative header-text hover:text-primary dark:hover:text-primary font-semibold transition">
                    Home
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/raspored" class="group relative header-text hover:text-primary dark:hover:text-primary font-semibold transition">
                    Spielplan
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/tablice" class="group relative header-text hover:text-primary dark:hover:text-primary font-semibold transition">
                    Tabelle
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/seniori" class="group relative header-text hover:text-primary dark:hover:text-primary font-semibold transition">
                    Teams
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/kontakt" class="group relative header-text hover:text-primary dark:hover:text-primary font-semibold transition">
                    Kontakt
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary group-hover:w-full transition-all duration-300"></span>
                </a>
            </div>

            <div class="flex items-center space-x-4">
                <button @click="darkMode = !darkMode" class="p-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-primary/10 dark:hover:bg-gray-700 transition group">
                    <svg x-show="!darkMode" class="w-5 h-5 text-gray-700 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <svg x-show="darkMode" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </button>
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2.5 rounded-lg bg-primary hover:bg-primary text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobileMenuOpen" x-transition class="lg:hidden header-bg dark:bg-gray-900 border-t dark:border-gray-800">
        <div class="container mx-auto px-4 py-4 space-y-1">
            <a href="/" class="block py-3 px-4 header-text hover:bg-primary/10 dark:hover:bg-gray-800 hover:text-primary dark:hover:text-primary rounded-lg font-semibold transition">Home</a>
            <a href="/raspored" class="block py-3 px-4 header-text hover:bg-primary/10 dark:hover:bg-gray-800 hover:text-primary dark:hover:text-primary rounded-lg font-semibold transition">Spielplan</a>
            <a href="/tablice" class="block py-3 px-4 header-text hover:bg-primary/10 dark:hover:bg-gray-800 hover:text-primary dark:hover:text-primary rounded-lg font-semibold transition">Tabelle</a>
            <a href="/seniori" class="block py-3 px-4 header-text hover:bg-primary/10 dark:hover:bg-gray-800 hover:text-primary dark:hover:text-primary rounded-lg font-semibold transition">Teams</a>
            <a href="/kontakt" class="block py-3 px-4 header-text hover:bg-primary/10 dark:hover:bg-gray-800 hover:text-primary dark:hover:text-primary rounded-lg font-semibold transition">Kontakt</a>
        </div>
    </div>
</nav>
