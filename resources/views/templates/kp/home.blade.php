@extends('templates.kp.layout')

@section('title', $settings->website_name ?? 'Home')

@section('hero')
<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">{{ $settings->website_name ?? 'Willkommen' }}</h1>
        <p class="text-xl text-blue-100">{{ $settings->tagline ?? 'Dein Fußballverein' }}</p>
    </div>
</section>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    @if($nextMatch)
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Nächstes Spiel</h2>
        <div class="text-center">
            <p class="text-gray-600 mb-2">{{ \Carbon\Carbon::parse($nextMatch->date_time_local)->format('d.m.Y H:i') }}</p>
            <p class="text-xl font-semibold">{{ $nextMatch->team_name_home }} - {{ $nextMatch->team_name_away }}</p>
        </div>
    </div>
    @endif

    @if($lastMatch)
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Letztes Ergebnis</h2>
        <div class="text-center">
            <p class="text-gray-600 mb-2">{{ \Carbon\Carbon::parse($lastMatch->date_time_local)->format('d.m.Y') }}</p>
            <p class="text-xl font-semibold">
                {{ $lastMatch->team_name_home }} {{ $lastMatch->team_score_home }}:{{ $lastMatch->team_score_away }} {{ $lastMatch->team_name_away }}
            </p>
        </div>
    </div>
    @endif
</div>

@if($topScorers && $topScorers->count() > 0)
<div class="mt-8 bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-4">Top Torschützen</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($topScorers->take(5) as $scorer)
        <div class="flex justify-between items-center border-b pb-2">
            <span class="font-medium">{{ $scorer->player_name }}</span>
            <span class="text-blue-600 font-bold">{{ $scorer->goals }} Tore</span>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
