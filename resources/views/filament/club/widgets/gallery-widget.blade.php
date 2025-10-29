<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📸 Galerie
        </x-slot>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($this->getGalleryImages() as $image)
                <div class="group relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-all cursor-pointer">
                    <img src="{{ $image['url'] }}" alt="{{ $image['title'] }}" class="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-3">
                        <p class="text-white font-semibold text-sm">{{ $image['title'] }}</p>
                        <p class="text-gray-300 text-xs">{{ $image['date'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 text-center">
            <a href="#" class="text-primary-600 dark:text-primary-400 hover:underline text-sm font-medium">
                Zur vollständigen Galerie →
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
