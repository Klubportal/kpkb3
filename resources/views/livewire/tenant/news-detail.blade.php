<div>
    {{-- BREADCRUMB --}}
    <div class="bg-base-200 py-4">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="/" wire:navigate>Home</a></li>
                    <li><a href="/news" wire:navigate>News</a></li>
                    <li>{{ $news->title }}</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ARTICLE HEADER --}}
    <article class="max-w-5xl mx-auto px-4 py-12">
        {{-- FEATURED IMAGE --}}
        @if($news->getFirstMediaUrl('featured'))
            <figure class="mb-8 rounded-2xl overflow-hidden shadow-2xl">
                <img src="{{ $news->getFirstMediaUrl('featured') }}"
                     alt="{{ $news->title }}"
                     class="w-full h-[500px] object-cover">
            </figure>
        @endif

        {{-- TITLE & META --}}
        <header class="mb-8">
            <h1 class="text-5xl font-bold text-base-content mb-4">{{ $news->title }}</h1>

            <div class="flex flex-wrap items-center gap-6 text-base-content/60 mb-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    {{ $news->published_at->format('d.m.Y H:i') }} Uhr
                </div>

                @if($news->author)
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        {{ $news->author->name }}
                    </div>
                @endif

                @if($news->team)
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        {{ $news->team->name }}
                    </div>
                @endif
            </div>

            {{-- TAGS --}}
            @if($news->tags->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($news->tags as $tag)
                        <span class="badge badge-primary">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </header>

        {{-- EXCERPT --}}
        @if($news->excerpt)
            <div class="text-xl text-base-content/80 font-medium mb-8 p-6 bg-base-200 rounded-xl border-l-4 border-primary">
                {{ $news->excerpt }}
            </div>
        @endif

        {{-- CONTENT --}}
        <div class="prose prose-lg max-w-none">
            {!! $news->content !!}
        </div>

        {{-- GALLERY --}}
        @if($news->getMedia('gallery')->count() > 0)
            <div class="mt-12">
                <h2 class="text-2xl font-bold mb-6">Bildergalerie</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($news->getMedia('gallery') as $image)
                        <figure class="rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition-shadow cursor-pointer">
                            <img src="{{ $image->getUrl() }}"
                                 alt="Gallery Image"
                                 class="w-full h-64 object-cover hover:scale-110 transition-transform duration-500">
                        </figure>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ACTIONS --}}
        <div class="flex items-center gap-4 mt-12 pt-8 border-t">
            <a href="/news" wire:navigate class="btn btn-outline">
                ← Zurück zur Übersicht
            </a>

            {{-- SHARE BUTTONS --}}
            <div class="flex-1"></div>
            <div class="flex gap-2">
                <button class="btn btn-circle btn-sm"
                        onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank', 'width=600,height=400')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </button>
                <button class="btn btn-circle btn-sm"
                        onclick="window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent('{{ $news->title }}'), '_blank', 'width=600,height=400')">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                </button>
            </div>
        </div>
    </article>

    {{-- RELATED NEWS --}}
    @if($relatedNews->count() > 0)
        <section class="bg-base-200 py-16 mt-12">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold mb-8">Ähnliche News</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedNews as $related)
                        <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow">
                            @if($related->getFirstMediaUrl('featured'))
                                <figure class="h-40 overflow-hidden">
                                    <img src="{{ $related->getFirstMediaUrl('featured') }}"
                                         alt="{{ $related->title }}"
                                         class="w-full h-full object-cover">
                                </figure>
                            @endif

                            <div class="card-body">
                                <h3 class="card-title text-lg line-clamp-2">{{ $related->title }}</h3>
                                <p class="text-sm text-base-content/60">
                                    {{ $related->published_at->format('d.m.Y') }}
                                </p>
                                <div class="card-actions justify-end mt-2">
                                    <a href="/news/{{ $related->slug }}" wire:navigate class="btn btn-sm btn-primary">
                                        Lesen →
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
