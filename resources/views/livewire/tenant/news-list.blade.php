<div>
    {{-- PAGE HEADER --}}
    <div class="bg-gradient-to-r from-primary to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-5xl font-bold mb-4">News & Aktuelles</h1>
            <p class="text-xl text-white/90">Bleib auf dem Laufenden</p>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="bg-base-200 py-6">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row gap-4">
                {{-- SEARCH --}}
                <div class="flex-1">
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Suche nach News..."
                           class="input input-bordered w-full">
                </div>

                {{-- TAG FILTER --}}
                @if($allTags->count() > 0)
                <div class="w-full md:w-64">
                    <select wire:model.live="selectedTag" class="select select-bordered w-full">
                        <option value="">Alle Kategorien</option>
                        @foreach($allTags as $tag)
                            <option value="{{ $tag->name }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- NEWS GRID --}}
    <section class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($news as $item)
                <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    @if($item->getFirstMediaUrl('featured'))
                        <figure class="h-48 overflow-hidden">
                            <img src="{{ $item->getFirstMediaUrl('featured') }}"
                                 alt="{{ $item->title }}"
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
                            {{ $item->published_at->format('d.m.Y') }}
                        </div>

                        <h3 class="card-title text-xl line-clamp-2">{{ $item->title }}</h3>

                        @if($item->excerpt)
                            <p class="text-base-content/70 line-clamp-3">{{ $item->excerpt }}</p>
                        @endif

                        @if($item->tags->count() > 0)
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($item->tags->take(2) as $tag)
                                    <span class="badge badge-primary badge-sm">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="card-actions justify-end mt-4">
                            <a href="/news/{{ $item->slug }}" class="btn btn-primary btn-sm">
                                Weiterlesen →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-24 h-24 mx-auto text-base-content/20 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <p class="text-base-content/60 text-lg">Keine News gefunden.</p>
                    @if($search || $selectedTag)
                        <button wire:click="$set('search', '')" class="btn btn-primary btn-sm mt-4">
                            Filter zurücksetzen
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($news->hasPages())
            <div class="mt-12">
                {{ $news->links() }}
            </div>
        @endif
    </section>
</div>
