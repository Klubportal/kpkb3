<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🏆 NEST - Events & Statistiken
        </x-slot>

        @php
            $data = $this->getNestData();
        @endphp

        {{-- Nächstes Event --}}
        <div class="p-6 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📅 Nächstes Event</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Typ</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $data['next_event']['type'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Datum</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $data['next_event']['date'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Zeit</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $data['next_event']['time'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Teilnehmer</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $data['next_event']['participants'] }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">
                📍 {{ $data['next_event']['location'] }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Kommende Events --}}
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">📋 Kommende Events</h3>
                <div class="space-y-2">
                    @foreach($data['upcoming_events'] as $event)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $event['title'] }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $event['date'] }}</p>
                            </div>
                            <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 text-xs rounded">
                                {{ $event['type'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Team-Metriken --}}
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">📈 Team-Metriken</h3>
                <div class="space-y-3">
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300">Trainingsbeteiligung</p>
                            <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $data['team_metrics']['training_attendance'] }}%</p>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $data['team_metrics']['training_attendance'] }}%"></div>
                        </div>
                    </div>

                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">Durchschnittsalter</p>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $data['team_metrics']['average_age'] }} Jahre</p>
                        </div>
                    </div>

                    <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">Aktive Mitglieder</p>
                            <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $data['team_metrics']['active_members'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
