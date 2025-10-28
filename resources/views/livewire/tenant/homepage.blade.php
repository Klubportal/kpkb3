<div>
    {{-- HERO SECTION --}}
    @if($featuredNews)
    <section class="relative h-[600px] bg-gradient-to-r from-primary to-secondary overflow-hidden">
        <div class="absolute inset-0">
            @if($featuredNews->getFirstMediaUrl('featured'))
                <img src="{{ $featuredNews->getFirstMediaUrl('featured') }}"
                     alt="{{ $featuredNews->title }}"
                     class="w-full h-full object-cover opacity-40">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 h-full flex items-center">
            <div class="max-w-3xl text-white">
                <div class="inline-block px-4 py-2 bg-primary/80 rounded-full text-sm font-semibold mb-4">
                    ⚽ FEATURED NEWS
                </div>
                <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
                    {{ $featuredNews->title }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-white/90">
                    {{ $featuredNews->excerpt }}
                </p>
                <a href="/news/{{ $featuredNews->slug }}"
                   class="btn btn-primary btn-lg gap-2">
                    Mehr erfahren
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>
    @else
    <section class="hero min-h-[500px] bg-gradient-to-r from-primary to-secondary">
        <div class="hero-content text-center text-white">
            <div class="max-w-2xl">
                <h1 class="text-6xl font-bold mb-6">Willkommen beim Testclub</h1>
                <p class="text-2xl mb-8">Tradition, Leidenschaft & Gemeinschaft seit 1965</p>
                <a href="/contact" class="btn btn-outline btn-lg text-white border-white hover:bg-white hover:text-primary">
                    Mitglied werden
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- LATEST NEWS --}}
    <section class="max-w-7xl mx-auto px-4 py-16">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-4xl font-bold text-base-content">Aktuelle News</h2>
                <p class="text-base-content/60 mt-2">Die neuesten Nachrichten aus unserem Verein</p>
            </div>
            <a href="/news" class="btn btn-primary btn-outline">
                Alle News ansehen
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($latestNews as $news)
                <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    @if($news->getFirstMediaUrl('featured'))
                        <figure class="h-48 overflow-hidden">
                            <img src="{{ $news->getFirstMediaUrl('featured') }}"
                                 alt="{{ $news->title }}"
                                 class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                        </figure>
                    @else
                        <figure class="h-48 bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-20 h-20 text-base-content/20">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                            </svg>
                        </figure>
                    @endif

                    <div class="card-body">
                        <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            {{ $news->published_at->format('d.m.Y') }}
                        </div>

                        <h3 class="card-title text-xl line-clamp-2">{{ $news->title }}</h3>

                        @if($news->excerpt)
                            <p class="text-base-content/70 line-clamp-3">{{ $news->excerpt }}</p>
                        @endif

                        <div class="card-actions justify-end mt-4">
                            <a href="/news/{{ $news->slug }}" class="btn btn-primary btn-sm">
                                Weiterlesen →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-base-content/60 text-lg">Noch keine News vorhanden.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- UPCOMING EVENTS --}}
    <section class="bg-base-200 py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-4xl font-bold text-base-content">Kommende Termine</h2>
                    <p class="text-base-content/60 mt-2">Verpasse keine Veranstaltung</p>
                </div>
                <a href="/events" class="btn btn-primary btn-outline">
                    Alle Termine
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($upcomingEvents as $event)
                    <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow">
                        <div class="card-body">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 text-center bg-primary text-primary-content rounded-lg p-3 min-w-[60px]">
                                    <div class="text-2xl font-bold">{{ $event->start_date->format('d') }}</div>
                                    <div class="text-xs uppercase">{{ $event->start_date->format('M') }}</div>
                                </div>

                                <div class="flex-1">
                                    <h3 class="font-bold text-lg line-clamp-2 mb-2">{{ $event->title }}</h3>

                                    <div class="space-y-1 text-sm text-base-content/70">
                                        <div class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            {{ $event->start_date->format('H:i') }} Uhr
                                        </div>

                                        @if($event->location)
                                            <div class="flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                                {{ $event->location }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-4">
                                        <a href="/events/{{ $event->id }}" class="btn btn-xs btn-primary btn-outline">
                                            Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-4 text-center py-12">
                        <p class="text-base-content/60 text-lg">Keine kommenden Termine.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- CLUB INFO / STATS --}}
    <section class="max-w-7xl mx-auto px-4 py-16">
        <div class="stats stats-vertical lg:stats-horizontal shadow-xl w-full">
            <div class="stat place-items-center">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-12 h-12">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <div class="stat-title">Mitglieder</div>
                <div class="stat-value text-primary">450+</div>
                <div class="stat-desc">Aktiv & Passiv</div>
            </div>

            <div class="stat place-items-center">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-12 h-12">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                    </svg>
                </div>
                <div class="stat-title">Teams</div>
                <div class="stat-value text-secondary">8</div>
                <div class="stat-desc">Von U10 bis Senioren</div>
            </div>

            <div class="stat place-items-center">
                <div class="stat-figure text-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-12 h-12">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                    </svg>
                </div>
                <div class="stat-title">Jahre</div>
                <div class="stat-value text-accent">59</div>
                <div class="stat-desc">Seit 1965</div>
            </div>
        </div>
    </section>
</div>
