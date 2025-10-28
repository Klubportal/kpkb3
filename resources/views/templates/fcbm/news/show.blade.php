@extends('templates.fcbm.layout')

@section('title', $news->title . ' - ' . ($settings->website_name ?? 'Fußballverein'))

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-8 text-sm">
            <a href="{{ url('/') }}" class="text-primary hover:underline">Home</a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="{{ url('/news') }}" class="text-primary hover:underline">News</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-600">{{ $news->title }}</span>
        </nav>

        <!-- Article Header -->
        <article class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Featured Image -->
            @if($news->featured_image)
            <img src="{{ asset('storage/' . $news->featured_image) }}"
                 alt="{{ $news->title }}"
                 class="w-full h-96 object-cover">
            @endif

            <div class="p-8">
                <!-- Tags -->
                @if($news->tags->count() > 0)
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($news->tags as $tag)
                    <a href="{{ url('/news?tag=' . urlencode($tag->name)) }}"
                       class="bg-badge text-badge text-sm px-3 py-1 rounded-full hover:opacity-80 transition">
                        {{ $tag->name }}
                    </a>
                    @endforeach
                </div>
                @endif

                <!-- Title -->
                <h1 class="text-4xl font-bold text-header mb-4">{{ $news->title }}</h1>

                <!-- Meta Info -->
                <div class="flex items-center gap-6 text-gray-600 mb-8 pb-8 border-b">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $news->published_at ? $news->published_at->format('d.m.Y H:i') : $news->created_at->format('d.m.Y H:i') }} Uhr
                    </span>
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        {{ $news->views_count }} Aufrufe
                    </span>
                </div>

                <!-- Content -->
                <div class="prose prose-lg max-w-none">
                    {!! nl2br(e($news->content)) !!}
                </div>

                <!-- Share Buttons -->
                <div class="mt-12 pt-8 border-t">
                    <h3 class="text-lg font-semibold mb-4">Artikel teilen:</h3>
                    <div class="flex gap-4">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/news/' . $news->slug)) }}"
                           target="_blank"
                           class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url('/news/' . $news->slug)) }}&text={{ urlencode($news->title) }}"
                           target="_blank"
                           class="flex items-center px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            Twitter
                        </a>
                    </div>
                </div>
            </div>
        </article>

        <!-- Related News -->
        @if($relatedNews->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-header mb-6">Weitere Nachrichten</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedNews as $related)
                <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                    @if($related->featured_image)
                    <a href="{{ url('/news/' . $related->slug) }}">
                        <img src="{{ asset('storage/' . $related->featured_image) }}"
                             alt="{{ $related->title }}"
                             class="w-full h-40 object-cover">
                    </a>
                    @endif
                    <div class="p-4">
                        <h3 class="font-bold text-header mb-2">
                            <a href="{{ url('/news/' . $related->slug) }}" class="hover:text-primary transition">
                                {{ $related->title }}
                            </a>
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $related->published_at ? $related->published_at->format('d.m.Y') : $related->created_at->format('d.m.Y') }}
                        </p>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Back to News -->
        <div class="mt-8">
            <a href="{{ url('/news') }}" class="inline-flex items-center text-primary hover:underline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Zurück zur Übersicht
            </a>
        </div>
    </div>
</div>
@endsection
