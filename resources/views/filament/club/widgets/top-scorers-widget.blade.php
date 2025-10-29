<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🥇 Torschützenliste
        </x-slot>

        <div class="space-y-2">
            @forelse($this->getTopScorers() as $index => $scorer)
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-400 text-yellow-900' : ($index === 1 ? 'bg-gray-400 text-gray-900' : ($index === 2 ? 'bg-orange-400 text-orange-900' : 'bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300')) }} font-bold">
                        {{ $index + 1 }}
                    </div>

                    @if($scorer['logo'])
                        <img src="{{ $scorer['logo'] }}" alt="{{ $scorer['club'] }}" class="w-8 h-8 object-contain">
                    @endif

                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $scorer['name'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $scorer['club'] }}</p>
                    </div>

                    <div class="text-right">
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $scorer['goals'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">Tore</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">Keine Torschützen verfügbar</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
