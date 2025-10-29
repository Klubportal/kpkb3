<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            ⚽ Top Spieler
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getPlayers() as $player)
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    @if($player['image'])
                        <img src="{{ $player['image'] }}" alt="{{ $player['name'] }}" class="w-12 h-12 object-cover rounded-full">
                    @else
                        <div class="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                            <span class="text-lg font-bold text-gray-600 dark:text-gray-300">{{ substr($player['name'], 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $player['name'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $player['position'] }} • #{{ $player['number'] }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">Keine Spieler verfügbar</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
