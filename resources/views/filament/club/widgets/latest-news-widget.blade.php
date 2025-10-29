<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📰 Aktuelle News
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getNews() as $newsItem)
                <div class="flex gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    @if($newsItem['image'])
                        <img src="{{ $newsItem['image'] }}" alt="{{ $newsItem['title'] }}" class="w-20 h-20 object-cover rounded">
                    @endif
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $newsItem['title'] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $newsItem['excerpt'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">{{ $newsItem['published_at'] }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">Keine News verfügbar</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
