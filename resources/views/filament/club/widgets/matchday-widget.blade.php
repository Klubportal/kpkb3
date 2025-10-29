<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📅 Aktueller Spieltag
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getCurrentMatchday() as $match)
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center justify-between gap-4">
                        <!-- Heim-Team -->
                        <div class="flex-1 flex items-center gap-2">
                            @if($match['home_logo'])
                                <img src="{{ $match['home_logo'] }}" alt="{{ $match['home_team'] }}" class="w-8 h-8">
                            @endif
                            <span class="font-semibold text-gray-900 dark:text-white">{{ Str::limit($match['home_team'], 20) }}</span>
                        </div>

                        <!-- Ergebnis/Status -->
                        <div class="text-center min-w-[80px]">
                            @if($match['status'] === 'FINISHED')
                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $match['home_score'] ?? 0 }} : {{ $match['away_score'] ?? 0 }}
                                </p>
                                <p class="text-xs text-green-600 dark:text-green-400">Beendet</p>
                            @elseif($match['status'] === 'LIVE')
                                <p class="text-xl font-bold text-red-600 dark:text-red-400">
                                    {{ $match['home_score'] ?? 0 }} : {{ $match['away_score'] ?? 0 }}
                                </p>
                                <p class="text-xs text-red-600 dark:text-red-400 animate-pulse">● LIVE</p>
                            @else
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $match['time'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ $match['date'] }}</p>
                            @endif
                        </div>

                        <!-- Auswärts-Team -->
                        <div class="flex-1 flex items-center justify-end gap-2">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ Str::limit($match['away_team'], 20) }}</span>
                            @if($match['away_logo'])
                                <img src="{{ $match['away_logo'] }}" alt="{{ $match['away_team'] }}" class="w-8 h-8">
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">Keine Spiele für aktuellen Spieltag</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
