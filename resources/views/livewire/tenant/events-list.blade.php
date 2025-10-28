<div>
    {{-- PAGE HEADER --}}
    <div class="bg-gradient-to-r from-primary to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-5xl font-bold mb-4">Termine & Events</h1>
            <p class="text-xl text-white/90">Alle Veranstaltungen im Überblick</p>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="bg-base-200 py-6">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row gap-4">
                {{-- TYPE FILTER --}}
                <div class="w-full md:w-64">
                    <select wire:model.live="filterType" class="select select-bordered w-full">
                        <option value="all">Alle Typen</option>
                        <option value="match">Spiel</option>
                        <option value="training">Training</option>
                        <option value="meeting">Treffen</option>
                        <option value="event">Veranstaltung</option>
                        <option value="tournament">Turnier</option>
                        <option value="other">Sonstiges</option>
                    </select>
                </div>

                {{-- VISIBILITY FILTER --}}
                <div class="w-full md:w-64">
                    <select wire:model.live="filterVisibility" class="select select-bordered w-full">
                        <option value="all">Alle Sichtbarkeiten</option>
                        <option value="public">Öffentlich</option>
                        <option value="members">Nur Mitglieder</option>
                        <option value="team">Nur Team</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- EVENTS GRID --}}
    <section class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($events as $event)
                <div class="card bg-base-100 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="card-body">
                        {{-- TYPE BADGE --}}
                        <div class="flex items-center gap-2 mb-2">
                            @switch($event->type)
                                @case('match')
                                    <span class="badge badge-error">Spiel</span>
                                    @break
                                @case('training')
                                    <span class="badge badge-info">Training</span>
                                    @break
                                @case('meeting')
                                    <span class="badge badge-warning">Treffen</span>
                                    @break
                                @case('tournament')
                                    <span class="badge badge-success">Turnier</span>
                                    @break
                                @default
                                    <span class="badge badge-ghost">{{ ucfirst($event->type) }}</span>
                            @endswitch

                            @if($event->visibility !== 'public')
                                <span class="badge badge-outline badge-sm">
                                    @if($event->visibility === 'members') Mitglieder
                                    @elseif($event->visibility === 'team') Team
                                    @endif
                                </span>
                            @endif
                        </div>

                        {{-- DATE DISPLAY --}}
                        <div class="flex items-start gap-4 mb-4">
                            <div class="flex-shrink-0 text-center bg-primary text-primary-content rounded-lg p-3 min-w-[70px]">
                                <div class="text-3xl font-bold">{{ $event->start_date->format('d') }}</div>
                                <div class="text-sm uppercase">{{ $event->start_date->format('M') }}</div>
                                <div class="text-xs">{{ $event->start_date->format('Y') }}</div>
                            </div>

                            <div class="flex-1">
                                <h3 class="card-title text-xl line-clamp-2">{{ $event->title }}</h3>
                            </div>
                        </div>

                        {{-- EVENT INFO --}}
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-base-content/70">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $event->start_date->format('H:i') }} Uhr
                                @if($event->end_date)
                                    - {{ $event->end_date->format('H:i') }} Uhr
                                @endif
                            </div>

                            @if($event->location)
                                <div class="flex items-center gap-2 text-base-content/70">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <span class="line-clamp-1">{{ $event->location }}</span>
                                </div>
                            @endif

                            @if($event->team)
                                <div class="flex items-center gap-2 text-base-content/70">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                    </svg>
                                    {{ $event->team->name }}
                                </div>
                            @endif
                        </div>

                        @if($event->description)
                            <p class="text-base-content/70 text-sm line-clamp-2 mt-3">{{ $event->description }}</p>
                        @endif

                        <div class="card-actions justify-end mt-4">
                            <a href="/events/{{ $event->id }}" class="btn btn-primary btn-sm">
                                Details →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-24 h-24 mx-auto text-base-content/20 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <p class="text-base-content/60 text-lg">Keine Termine gefunden.</p>
                    @if($filterType !== 'all' || $filterVisibility !== 'all')
                        <button wire:click="$set('filterType', 'all')" class="btn btn-primary btn-sm mt-4">
                            Filter zurücksetzen
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($events->hasPages())
            <div class="mt-12">
                {{ $events->links() }}
            </div>
        @endif
    </section>
</div>
