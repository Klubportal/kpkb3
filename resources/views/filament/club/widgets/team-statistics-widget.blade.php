<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📊 Team-Statistiken
        </x-slot>

        @php
            $stats = $this->getStatistics();
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Spiele Gesamt -->
            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_matches'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Spiele</p>
            </div>

            <!-- Siege -->
            <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['won'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Siege</p>
            </div>

            <!-- Unentschieden -->
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-3xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['drawn'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Unentschieden</p>
            </div>

            <!-- Niederlagen -->
            <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['lost'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Niederlagen</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            <!-- Siegquote -->
            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['win_rate'] }}%</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Siegquote</p>
            </div>

            <!-- Kader -->
            <div class="text-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['total_players'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Spieler</p>
            </div>

            <!-- Tore erzielt -->
            <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['goals_scored'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Tore erzielt</p>
            </div>

            <!-- Tordifferenz -->
            <div class="text-center p-4 bg-{{ $stats['goal_difference'] >= 0 ? 'green' : 'red' }}-50 dark:bg-{{ $stats['goal_difference'] >= 0 ? 'green' : 'red' }}-900/20 rounded-lg">
                <p class="text-3xl font-bold text-{{ $stats['goal_difference'] >= 0 ? 'green' : 'red' }}-600 dark:text-{{ $stats['goal_difference'] >= 0 ? 'green' : 'red' }}-400">
                    {{ $stats['goal_difference'] > 0 ? '+' : '' }}{{ $stats['goal_difference'] }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Tordifferenz</p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
