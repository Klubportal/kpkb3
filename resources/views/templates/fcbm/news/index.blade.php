@extends('templates.fcbm.layout')

@section('title', 'News - ' . ($settings->website_name ?? 'Fußballverein'))

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-header mb-4">Aktuelle News</h1>
        <p class="text-gray-600">Bleiben Sie auf dem Laufenden mit den neuesten Nachrichten</p>
    </div>

    <!-- Tag Filter -->
    @if($tags->count() > 0)
    <div class="mb-8 flex flex-wrap gap-2">
        <a href="{{ url('/news') }}"
           class="px-4 py-2 rounded-full text-sm font-semibold transition {{ !$tag ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Alle
        </a>
        @foreach($tags as $tagName)
        <a href="{{ url('/news?tag=' . urlencode($tagName)) }}"
           class="px-4 py-2 rounded-full text-sm font-semibold transition {{ $tag === $tagName ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            {{ $tagName }}
        </a>
        @endforeach
    </div>
    @endif

    <!-- News Grid -->
    @if($news->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        @foreach($news as $article)
        <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
            <!-- Featured Image -->
            @if($article->featured_image)
            <a href="{{ url('/news/' . $article->slug) }}">
                <img src="{{ asset('storage/' . $article->featured_image) }}"
                     alt="{{ $article->title }}"
                     class="w-full h-48 object-cover">
            </a>
            @else
            <a href="{{ url('/news/' . $article->slug) }}">
                <div class="w-full h-48 bg-gradient-primary flex items-center justify-center">
                    <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
            </a>
            @endif

            <div class="p-6">
                <!-- Tags -->
                @if($article->tags->count() > 0)
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach($article->tags as $articleTag)
                    <span class="bg-badge text-badge text-xs px-2 py-1 rounded-full">{{ $articleTag->name }}</span>
                    @endforeach
                </div>
                @endif

                <!-- Title -->
                <h2 class="text-xl font-bold text-header mb-2">
                    <a href="{{ url('/news/' . $article->slug) }}" class="hover:text-primary transition">
                        {{ $article->title }}
                    </a>
                </h2>

                <!-- Excerpt -->
                @if($article->excerpt)
                <p class="text-gray-600 mb-4 line-clamp-3">{{ $article->excerpt }}</p>
                @endif

                <!-- Meta -->
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <span>{{ $article->published_at ? $article->published_at->format('d.m.Y') : $article->created_at->format('d.m.Y') }}</span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        {{ $article->views_count }}
                    </span>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $news->links() }}
    </div>
    @else
    <div class="bg-gray-100 rounded-lg p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Keine News verfügbar</h3>
        <p class="text-gray-500">Aktuell gibt es keine veröffentlichten Nachrichten.</p>
    </div>
    @endif
</div>
@endsection
