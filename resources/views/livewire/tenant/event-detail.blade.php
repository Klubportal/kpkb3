<div>
    {{-- BREADCRUMB --}}
    <div class="bg-base-200 py-4">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="/" wire:navigate>Home</a></li>
                    <li><a href="/events" wire:navigate>Termine</a></li>
                    <li>{{ $event->title }}</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- EVENT DETAIL --}}
    <article class="max-w-5xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- MAIN CONTENT --}}
            <div class="lg:col-span-2">
                {{-- HEADER --}}
                <header class="mb-8">
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        @switch($event->type)
                            @case('match')
                                <span class="badge badge-error badge-lg">⚽ Spiel</span>
                                @break
                            @case('training')
                                <span class="badge badge-info badge-lg">🏋️ Training</span>
                                @break
                            @case('meeting')
                                <span class="badge badge-warning badge-lg">💼 Treffen</span>
                                @break
                            @case('tournament')
                                <span class="badge badge-success badge-lg">🏆 Turnier</span>
                                @break
                            @default
                                <span class="badge badge-ghost badge-lg">{{ ucfirst($event->type) }}</span>
                        @endswitch

                        @if($event->visibility !== 'public')
                            <span class="badge badge-outline">
                                @if($event->visibility === 'members') 👥 Nur Mitglieder
                                @elseif($event->visibility === 'team') 🔒 Nur Team
                                @endif
                            </span>
                        @endif
                    </div>

                    <h1 class="text-5xl font-bold text-base-content mb-6">{{ $event->title }}</h1>
                </header>

                {{-- DESCRIPTION --}}
                @if($event->description)
                    <div class="prose prose-lg max-w-none mb-8">
                        <p>{{ $event->description }}</p>
                    </div>
                @endif

                {{-- NOTES --}}
                @if($event->notes)
                    <div class="alert alert-info mb-8">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <div>
                            <h3 class="font-bold">Wichtige Hinweise</h3>
                            <div class="text-sm">{{ $event->notes }}</div>
                        </div>
                    </div>
                @endif

                {{-- PARTICIPANTS --}}
                @if($event->participants && count($event->participants) > 0)
                    <div class="card bg-base-100 shadow-lg mb-8">
                        <div class="card-body">
                            <h2 class="card-title">Teilnehmer ({{ count($event->participants) }})</h2>
                            <div class="flex flex-wrap gap-2 mt-4">
                                @foreach($event->participants as $participant)
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                                            <span class="text-xs">{{ strtoupper(substr($participant, 0, 2)) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- MATCH DETAILS --}}
                @if($event->type === 'match' && ($event->opponent || $event->result))
                    <div class="card bg-base-100 shadow-lg">
                        <div class="card-body">
                            <h2 class="card-title">Spieldetails</h2>

                            @if($event->opponent)
                                <div class="flex items-center justify-between text-2xl font-bold my-6">
                                    <div class="text-center flex-1">
                                        @if($event->team)
                                            <div class="text-sm text-base-content/60 mb-2">Heimmannschaft</div>
                                            {{ $event->team->name }}
                                        @else
                                            Wir
                                        @endif
                                    </div>

                                    @if($event->result)
                                        <div class="badge badge-lg badge-primary mx-4">{{ $event->result }}</div>
                                    @else
                                        <div class="text-base-content/40 mx-4">vs</div>
                                    @endif

                                    <div class="text-center flex-1">
                                        <div class="text-sm text-base-content/60 mb-2">Gastmannschaft</div>
                                        {{ $event->opponent }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- SIDEBAR --}}
            <div class="lg:col-span-1">
                {{-- DATE & TIME CARD --}}
                <div class="card bg-gradient-to-br from-primary to-secondary text-primary-content shadow-xl mb-6">
                    <div class="card-body items-center text-center">
                        <div class="text-6xl font-bold">{{ $event->start_date->format('d') }}</div>
                        <div class="text-2xl uppercase">{{ $event->start_date->format('F') }}</div>
                        <div class="text-lg">{{ $event->start_date->format('Y') }}</div>
                        <div class="divider"></div>
                        <div class="text-xl font-semibold">{{ $event->start_date->format('H:i') }} Uhr</div>
                        @if($event->end_date)
                            <div class="text-sm opacity-90">bis {{ $event->end_date->format('H:i') }} Uhr</div>
                        @endif
                    </div>
                </div>

                {{-- LOCATION CARD --}}
                @if($event->location)
                    <div class="card bg-base-100 shadow-lg mb-6">
                        <div class="card-body">
                            <h3 class="font-bold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                Ort
                            </h3>
                            <p class="mt-2">{{ $event->location }}</p>
                            @if($event->location_address)
                                <p class="text-sm text-base-content/60 mt-1">{{ $event->location_address }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- TEAM CARD --}}
                @if($event->team)
                    <div class="card bg-base-100 shadow-lg mb-6">
                        <div class="card-body">
                            <h3 class="font-bold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                                Team
                            </h3>
                            <p class="mt-2">{{ $event->team->name }}</p>
                        </div>
                    </div>
                @endif

                {{-- ACTIONS --}}
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-body gap-3">
                        <a href="/events" wire:navigate class="btn btn-outline w-full">
                            ← Alle Termine
                        </a>

                        <button class="btn btn-primary w-full gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            Zum Kalender hinzufügen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </article>

    {{-- UPCOMING EVENTS --}}
    @if($upcomingEvents->count() > 0)
        <section class="bg-base-200 py-16 mt-12">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold mb-8">Weitere Termine</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($upcomingEvents as $upcoming)
                        <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow">
                            <div class="card-body">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 text-center bg-primary text-primary-content rounded-lg p-2 min-w-[60px]">
                                        <div class="text-2xl font-bold">{{ $upcoming->start_date->format('d') }}</div>
                                        <div class="text-xs uppercase">{{ $upcoming->start_date->format('M') }}</div>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold line-clamp-2">{{ $upcoming->title }}</h3>
                                        <p class="text-sm text-base-content/60 mt-1">
                                            {{ $upcoming->start_date->format('H:i') }} Uhr
                                        </p>
                                    </div>
                                </div>
                                <div class="card-actions justify-end mt-2">
                                    <a href="/events/{{ $upcoming->id }}" wire:navigate class="btn btn-sm btn-primary">
                                        Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
