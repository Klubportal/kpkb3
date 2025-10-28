<header class="bg-header shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="container mx-auto px-4">
        <!-- Top Bar -->
        <div class="border-b border-gray-200 py-2">
            <div class="flex justify-between items-center text-sm">
                <div class="flex items-center space-x-4">
                    <span class="text-header">{{ now()->format('d.m.Y') }}</span>
                </div>
                <div class="flex items-center space-x-4">
                    @if($settings->facebook_url)
                        <a href="{{ $settings->facebook_url }}" target="_blank" class="text-header hover:text-primary transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    @endif
                    @if($settings->instagram_url)
                        <a href="{{ $settings->instagram_url }}" target="_blank" class="text-header hover:text-primary transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    @endif
                    @if($settings->youtube_url)
                        <a href="{{ $settings->youtube_url }}" target="_blank" class="text-header hover:text-primary transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <nav class="py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center space-x-3">
                    @if($settings->logo)
                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $settings->website_name }}" class="h-16 w-auto">
                    @else
                        <div class="h-16 w-16 bg-primary rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-2xl">{{ substr($settings->website_name ?? 'FC', 0, 2) }}</span>
                        </div>
                    @endif
                    <span class="text-2xl font-bold text-header hidden md:block">{{ $settings->website_name }}</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="{{ url('/') }}" class="text-header hover:text-primary transition font-semibold">Home</a>
                    <a href="{{ url('/news') }}" class="text-header hover:text-primary transition font-semibold">News</a>
                    <a href="#mannschaft" class="text-header hover:text-primary transition font-semibold">Mannschaft</a>
                    <a href="#spielplan" class="text-header hover:text-primary transition font-semibold">Spielplan</a>
                    <a href="#tabelle" class="text-header hover:text-primary transition font-semibold">Tabelle</a>
                    <a href="#tickets" class="bg-primary text-white px-6 py-2 rounded-full hover:opacity-90 transition font-semibold">Tickets</a>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-header">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" x-transition class="lg:hidden mt-4 pb-4">
                <div class="flex flex-col space-y-4">
                    <a href="{{ url('/') }}" class="text-header hover:text-primary transition font-semibold">Home</a>
                    <a href="{{ url('/news') }}" class="text-header hover:text-primary transition font-semibold">News</a>
                    <a href="#mannschaft" class="text-header hover:text-primary transition font-semibold">Mannschaft</a>
                    <a href="#spielplan" class="text-header hover:text-primary transition font-semibold">Spielplan</a>
                    <a href="#tabelle" class="text-header hover:text-primary transition font-semibold">Tabelle</a>
                    <a href="#tickets" class="bg-primary text-white px-6 py-2 rounded-full hover:opacity-90 transition font-semibold inline-block text-center">Tickets</a>
                </div>
            </div>
        </nav>
    </div>
</header>
