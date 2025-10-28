@php
    $settings = app(\App\Settings\GeneralSettings::class);
@endphp
<div>
    {{-- Hero Section --}}
    <div class="bg-gradient-to-br from-primary/10 to-secondary/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <x-mary-header title="Willkommen bei {{ $settings->site_name }}" subtitle="{{ $settings->site_description }}" size="text-5xl" class="mb-8" />

            <p class="text-xl text-base-content/80 max-w-3xl">
                Verwalte deinen Verein professionell mit unserem modernen Club Management System.
                News, Events, Teams, Spieler und vieles mehr - alles an einem Ort.
            </p>
        </div>
    </div>

    {{-- Featured News --}}
    @if($featuredNews->count() > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <x-mary-header title="Featured News" subtitle="Wichtige Nachrichten" class="mb-8" />

        <div class="grid md:grid-cols-3 gap-6">
            @foreach($featuredNews as $news)
                <x-mary-card class="hover:shadow-xl transition-shadow">
                    @if($news->getFirstMediaUrl('featured_image'))
                        <img src="{{ $news->getFirstMediaUrl('featured_image') }}"
                             alt="{{ $news->getTranslation('title', app()->getLocale()) }}"
                             class="w-full h-48 object-cover rounded-lg mb-4">
                    @endif

                    <x-slot:title>
                        {{ $news->getTranslation('title', app()->getLocale()) }}
                    </x-slot:title>

                    <p class="text-sm text-base-content/70 mb-4">
                        {{ Str::limit($news->getTranslation('excerpt', app()->getLocale()), 120) }}
                    </p>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-base-content/60">
                            {{ $news->published_at->format('d.m.Y') }}
                        </span>
                        <a href="{{ url('/news/' . $news->slug) }}" class="link link-primary">
                            Weiterlesen →
                        </a>
                    </div>
                </x-mary-card>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Latest News --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <x-mary-header title="Aktuelle News" subtitle="Die neuesten Nachrichten" class="mb-8" />

        @if($latestNews->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($latestNews as $news)
                    <x-mary-card class="hover:shadow-lg transition-shadow">
                        @if($news->getFirstMediaUrl('featured_image'))
                            <img src="{{ $news->getFirstMediaUrl('featured_image') }}"
                                 alt="{{ $news->getTranslation('title', app()->getLocale()) }}"
                                 class="w-full h-40 object-cover rounded-lg mb-4">
                        @endif

                        <x-slot:title>
                            <a href="{{ url('/news/' . $news->slug) }}" class="hover:text-primary transition">
                                {{ $news->getTranslation('title', app()->getLocale()) }}
                            </a>
                        </x-slot:title>

                        <p class="text-sm text-base-content/70 mb-4">
                            {{ Str::limit($news->getTranslation('excerpt', app()->getLocale()), 100) }}
                        </p>

                        {{-- Tags --}}
                        @if($news->tags->count() > 0)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($news->tags as $tag)
                                    <x-mary-badge value="{{ $tag->getTranslation('name', app()->getLocale()) }}" class="badge-sm" />
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-base-content/60">
                                {{ $news->published_at->format('d.m.Y') }}
                            </span>
                            <a href="{{ url('/news/' . $news->slug) }}" class="link link-primary">
                                Lesen →
                            </a>
                        </div>
                    </x-mary-card>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ url('/news') }}" class="btn btn-primary">
                    Alle News anzeigen
                </a>
            </div>
        @else
            <x-mary-alert icon="o-information-circle" class="alert-info">
                Noch keine News vorhanden. Erstelle deine ersten News im Admin-Bereich!
            </x-mary-alert>
        @endif
    </div>

    {{-- Features Section --}}
    <div class="bg-base-200 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-mary-header title="Features" subtitle="Was wir bieten" class="mb-12 text-center" />

            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="text-5xl mb-4">📰</div>
                    <h3 class="text-xl font-bold mb-3">News & Content</h3>
                    <p class="text-base-content/70">
                        Veröffentliche Neuigkeiten und Artikel in mehreren Sprachen
                    </p>
                </div>

                <div class="text-center">
                    <div class="text-5xl mb-4">👥</div>
                    <h3 class="text-xl font-bold mb-3">Teams & Spieler</h3>
                    <p class="text-base-content/70">
                        Verwalte Teams, Spieler und Trainer zentral
                    </p>
                </div>

                <div class="text-center">
                    <div class="text-5xl mb-4">🎯</div>
                    <h3 class="text-xl font-bold mb-3">Events & Spiele</h3>
                    <p class="text-base-content/70">
                        Plane Events und tracke alle Termine
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
