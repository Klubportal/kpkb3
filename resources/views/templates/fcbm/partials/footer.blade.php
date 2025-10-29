<footer class="bg-footer text-footer">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Column 1: About -->
            <div>
                @if($settings->logo)
                    <img src="{{ url('/storage/' . $settings->logo) }}" alt="{{ $settings->website_name }}" class="h-16 w-auto mb-4 brightness-0 invert">
                @else
                    <div class="h-16 w-16 bg-white rounded-full flex items-center justify-center mb-4">
                        <span class="text-primary font-bold text-2xl">{{ substr($settings->website_name ?? 'FC', 0, 2) }}</span>
                    </div>
                @endif
                <h3 class="font-bold text-lg mb-4">{{ $settings->website_name }}</h3>
                @if($settings->footer_about)
                    <p class="text-gray-300 text-sm leading-relaxed">{{ $settings->footer_about }}</p>
                @endif
            </div>

            <!-- Column 2: Quick Links -->
            <div>
                <h3 class="font-bold text-lg mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="text-gray-300 hover:text-white transition">Home</a></li>
                    <a href="{{ url('/news') }}" class="text-gray-300 hover:text-white transition">News</a>
                    <li><a href="#mannschaft" class="text-gray-300 hover:text-white transition">Mannschaft</a></li>
                    <li><a href="#spielplan" class="text-gray-300 hover:text-white transition">Spielplan</a></li>
                    <li><a href="#tabelle" class="text-gray-300 hover:text-white transition">Tabelle</a></li>
                </ul>
            </div>

            <!-- Column 3: Contact -->
            <div>
                <h3 class="font-bold text-lg mb-4">Kontakt</h3>
                <ul class="space-y-3 text-sm">
                    @if($settings->footer_email)
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:{{ $settings->footer_email }}" class="text-gray-300 hover:text-white transition">{{ $settings->footer_email }}</a>
                        </li>
                    @endif
                    @if($settings->footer_phone)
                        <li class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <a href="tel:{{ $settings->footer_phone }}" class="text-gray-300 hover:text-white transition">{{ $settings->footer_phone }}</a>
                        </li>
                    @endif
                    @if($settings->footer_address)
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-gray-300">{{ $settings->footer_address }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- Column 4: Social Media -->
            <div>
                <h3 class="font-bold text-lg mb-4">Folge uns</h3>
                <div class="flex space-x-4">
                    @if($settings->facebook_url)
                        <a href="{{ $settings->facebook_url }}" target="_blank" class="w-10 h-10 bg-gray-700 hover:bg-primary rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    @endif
                    @if($settings->instagram_url)
                        <a href="{{ $settings->instagram_url }}" target="_blank" class="w-10 h-10 bg-gray-700 hover:bg-primary rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    @endif
                    @if($settings->youtube_url)
                        <a href="{{ $settings->youtube_url }}" target="_blank" class="w-10 h-10 bg-gray-700 hover:bg-primary rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    @endif
                    @if($settings->twitter_url)
                        <a href="{{ $settings->twitter_url }}" target="_blank" class="w-10 h-10 bg-gray-700 hover:bg-primary rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-700 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm">© {{ date('Y') }} {{ $settings->website_name }}. Alle Rechte vorbehalten.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="text-gray-400 hover:text-white text-sm transition">Impressum</a>
                <a href="#" class="text-gray-400 hover:text-white text-sm transition">Datenschutz</a>
                <a href="#" class="text-gray-400 hover:text-white text-sm transition">AGBs</a>
            </div>
        </div>
    </div>
</footer>
