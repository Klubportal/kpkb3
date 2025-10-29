<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🏟️ Nächstes Spiel
        </x-slot>

        @if($match = $this->getNextMatch())
            <div class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-800 dark:to-gray-700 rounded-lg">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1 text-center">
                        @if($match['home_logo'])
                            <img src="{{ $match['home_logo'] }}" alt="{{ $match['home_team'] }}" class="w-16 h-16 mx-auto mb-2">
                        @endif
                        <p class="font-bold text-gray-900 dark:text-white">{{ $match['home_team'] }}</p>
                    </div>

                    <div class="text-center px-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">VS</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $match['date'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $match['time'] }}</p>
                    </div>

                    <div class="flex-1 text-center">
                        @if($match['away_logo'])
                            <img src="{{ $match['away_logo'] }}" alt="{{ $match['away_team'] }}" class="w-16 h-16 mx-auto mb-2">
                        @endif
                        <p class="font-bold text-gray-900 dark:text-white">{{ $match['away_team'] }}</p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-blue-200 dark:border-gray-600">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        <strong>Wettbewerb:</strong> {{ $match['competition'] }}
                    </p>
                    @if($match['location'])
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mt-1">
                            <strong>Ort:</strong> {{ $match['location'] }}
                        </p>
                    @endif
                </div>
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">Kein nächstes Spiel gefunden</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
