<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📊 Tabelle
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::button size="xs" color="gray" tag="a" href="#">
                Vollansicht →
            </x-filament::button>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider">#</th>
                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider">Team</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider">Sp</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider">S</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider">U</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider">N</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider">Tore</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider">Diff</th>
                        <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider font-bold">Pkt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getStandings() as $index => $team)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition
                            {{ $index < 3 ? 'bg-green-50/50 dark:bg-green-900/10' : '' }}
                            {{ $index >= 7 ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                            <td class="px-2 py-3 font-semibold">
                                <div class="flex items-center gap-1">
                                    {{ $team['position'] }}
                                    @if($index === 0)
                                        <span class="text-yellow-500">👑</span>
                                    @elseif($index < 3)
                                        <span class="text-green-500">▲</span>
                                    @elseif($index >= 7)
                                        <span class="text-red-500">▼</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-3">
                                <div class="flex items-center gap-2">
                                    @if($team['logo'])
                                        <img src="{{ $team['logo'] }}" alt="{{ $team['team'] }}" class="w-6 h-6 object-contain">
                                    @endif
                                    <span class="font-medium">{{ Str::limit($team['team'], 25) }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-3 text-center">{{ $team['played'] }}</td>
                            <td class="px-2 py-3 text-center text-green-600 dark:text-green-400 font-semibold">{{ $team['won'] }}</td>
                            <td class="px-2 py-3 text-center text-gray-600 dark:text-gray-400">{{ $team['drawn'] }}</td>
                            <td class="px-2 py-3 text-center text-red-600 dark:text-red-400 font-semibold">{{ $team['lost'] }}</td>
                            <td class="px-2 py-3 text-center text-xs">{{ $team['goals_for'] }}:{{ $team['goals_against'] }}</td>
                            <td class="px-2 py-3 text-center font-semibold {{ $team['goal_diff'] > 0 ? 'text-green-600 dark:text-green-400' : ($team['goal_diff'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400') }}">
                                {{ $team['goal_diff'] > 0 ? '+' : '' }}{{ $team['goal_diff'] }}
                            </td>
                            <td class="px-2 py-3 text-center font-bold text-lg">{{ $team['points'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-2 py-8 text-center text-gray-500 dark:text-gray-400">Keine Tabelle verfügbar</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Legende --}}
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 text-xs">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-100 dark:bg-green-900/20 border border-green-300 dark:border-green-700 rounded"></div>
                <span class="text-gray-600 dark:text-gray-400">Champions League</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-100 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded"></div>
                <span class="text-gray-600 dark:text-gray-400">Abstiegszone</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
