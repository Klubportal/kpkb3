@extends('templates.bm.layout')

@section('title', $settings->website_name ?? 'Home')

@section('hero')
<section class="relative hero-bg hero-text py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute transform rotate-12 bg-white w-96 h-96 rounded-full -top-48 -left-48"></div>
        <div class="absolute transform -rotate-12 bg-white w-96 h-96 rounded-full -bottom-48 -right-48"></div>
    </div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center max-w-4xl mx-auto">
            <h1 class="text-5xl md:text-6xl font-black mb-6 animate-fade-in hero-text">{{ $settings->website_name ?? 'Willkommen' }}</h1>
            <p class="text-2xl md:text-3xl hero-text opacity-90 font-bold mb-8">{{ $settings->slogan ?? 'Mia San Mia' }}</p>
            <div class="flex justify-center space-x-4">
                <a href="/raspored" class="bg-white text-primary px-8 py-4 rounded-lg font-bold hover:bg-gray-100 transition transform hover:scale-105 shadow-xl">
                    Spielplan ansehen
                </a>
                <a href="/seniori" class="hero-bg hero-text px-8 py-4 rounded-lg font-bold hover:opacity-90 transition transform hover:scale-105 border-2 border-white">
                    Unser Team
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 -mt-12 mb-12 relative z-20">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-8 text-center transform hover:scale-105 transition">
        <div class="w-16 h-16 badge-bg rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 badge-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <h3 class="text-3xl font-bold text-primary dark:text-primary mb-2">{{ $totalPlayers ?? 0 }}</h3>
        <p class="text-gray-600 dark:text-gray-300 font-semibold">Spieler</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-8 text-center transform hover:scale-105 transition">
        <div class="w-16 h-16 badge-bg rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 badge-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <h3 class="text-3xl font-bold text-primary dark:text-primary mb-2">{{ $totalMatches ?? 0 }}</h3>
        <p class="text-gray-600 dark:text-gray-300 font-semibold">Spiele</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-8 text-center transform hover:scale-105 transition">
        <div class="w-16 h-16 badge-bg rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 badge-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
        </div>
        <h3 class="text-3xl font-bold text-primary dark:text-primary mb-2">{{ $totalGoals ?? 0 }}</h3>
        <p class="text-gray-600 dark:text-gray-300 font-semibold">Tore</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
    @if($nextMatch)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 border-l-4 border-primary">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Nächstes Spiel</h2>
            <span class="bg-primary/10 dark:bg-primary/90 text-primary dark:text-red-300 px-4 py-1 rounded-full text-sm font-semibold">Upcoming</span>
        </div>
        <div class="text-center">
            <p class="text-gray-600 dark:text-gray-400 font-medium mb-4">{{ \Carbon\Carbon::parse($nextMatch->date_time_local)->format('d.m.Y H:i') }}</p>
            <div class="flex items-center justify-center space-x-6 text-xl font-bold text-gray-900 dark:text-white">
                <span class="flex-1 text-right">{{ $nextMatch->team_name_home }}</span>
                <span class="text-primary text-3xl">VS</span>
                <span class="flex-1 text-left">{{ $nextMatch->team_name_away }}</span>
            </div>
        </div>
    </div>
    @endif

    @if($lastMatch)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 border-l-4 border-secondary">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Letztes Ergebnis</h2>
            <span class="bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300 px-4 py-1 rounded-full text-sm font-semibold">Finished</span>
        </div>
        <div class="text-center">
            <p class="text-gray-600 dark:text-gray-400 font-medium mb-4">{{ \Carbon\Carbon::parse($lastMatch->date_time_local)->format('d.m.Y') }}</p>
            <div class="flex items-center justify-center space-x-6">
                <div class="flex-1 text-right">
                    <p class="font-bold text-gray-900 dark:text-white mb-2">{{ $lastMatch->team_name_home }}</p>
                    <p class="text-4xl font-black text-primary">{{ $lastMatch->team_score_home }}</p>
                </div>
                <span class="text-gray-400 text-3xl font-bold">:</span>
                <div class="flex-1 text-left">
                    <p class="font-bold text-gray-900 dark:text-white mb-2">{{ $lastMatch->team_name_away }}</p>
                    <p class="text-4xl font-black text-primary">{{ $lastMatch->team_score_away }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@if($topScorers && $topScorers->count() > 0)
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
    <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white flex items-center">
        <svg class="w-8 h-8 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        Top Torschützen
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($topScorers->take(6) as $index => $scorer)
        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-red-50 dark:hover:bg-gray-600 transition">
            <div class="flex items-center space-x-4">
                <span class="text-2xl font-black text-primary dark:text-primary w-8">{{ $index + 1 }}</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $scorer->player_name }}</span>
            </div>
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"></path></svg>
                <span class="text-2xl font-black text-primary dark:text-primary">{{ $scorer->goals }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
