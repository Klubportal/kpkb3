<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-3">
                @if($settings->logo)
                <img src="/storage/{{ $settings->logo }}" alt="Logo" class="h-10 w-10 object-contain">
                @endif
                <span class="text-xl font-bold text-gray-800">{{ $settings->website_name ?? 'Verein' }}</span>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="/" class="text-gray-700 hover:text-blue-600 transition">Home</a>
                <a href="/raspored" class="text-gray-700 hover:text-blue-600 transition">Spielplan</a>
                <a href="/tablice" class="text-gray-700 hover:text-blue-600 transition">Tabelle</a>
                <a href="/seniori" class="text-gray-700 hover:text-blue-600 transition">Mannschaft</a>
                <a href="/kontakt" class="text-gray-700 hover:text-blue-600 transition">Kontakt</a>
            </div>
        </div>
    </div>
</nav>
