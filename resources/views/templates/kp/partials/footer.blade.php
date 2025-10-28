<footer class="bg-gray-900 text-white py-8 mt-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-bold mb-3">{{ $settings->website_name ?? 'Verein' }}</h3>
                <p class="text-gray-400 text-sm">{{ $settings->club_description ?? '' }}</p>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Kontakt</h4>
                <p class="text-gray-400 text-sm">{{ $settings->footer_email ?? '' }}</p>
                <p class="text-gray-400 text-sm">{{ $settings->footer_phone ?? '' }}</p>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Social Media</h4>
                <div class="flex space-x-4">
                    @if($settings->facebook_url)
                    <a href="{{ $settings->facebook_url }}" class="text-gray-400 hover:text-white">Facebook</a>
                    @endif
                    @if($settings->instagram_url)
                    <a href="{{ $settings->instagram_url }}" class="text-gray-400 hover:text-white">Instagram</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-6 pt-6 text-center text-gray-400 text-sm">
            <p>&copy; {{ date('Y') }} {{ $settings->website_name ?? 'Verein' }}. Alle Rechte vorbehalten.</p>
        </div>
    </div>
</footer>
