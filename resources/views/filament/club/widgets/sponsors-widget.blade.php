<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🤝 Unsere Sponsoren
        </x-slot>

        <div class="space-y-4">
            @foreach($this->getSponsors() as $sponsor)
                <a href="{{ $sponsor['website'] }}" target="_blank" class="block">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <img src="{{ $sponsor['logo'] }}" alt="{{ $sponsor['name'] }}" class="h-16 object-contain">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $sponsor['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                @if($sponsor['tier'] === 'platinum')
                                    <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded">Platin</span>
                                @elseif($sponsor['tier'] === 'gold')
                                    <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded">Gold</span>
                                @elseif($sponsor['tier'] === 'silver')
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">Silber</span>
                                @else
                                    <span class="px-2 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded">Bronze</span>
                                @endif
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Interessiert an einer Partnerschaft?
                <a href="#" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">Kontaktieren Sie uns</a>
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
