@extends('templates.kb.layout')

@section('title', 'Senioren - ' . ($settings->website_name ?? 'Verein'))

@php
    $primaryColor = $settings->primary_color ?? '#dc2626';
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Hero Section with dynamic color -->
    <div class="text-white rounded-lg p-8 mb-8 shadow-lg" style="background: linear-gradient(to right, {{ $primaryColor }}, {{ $primaryColor }}dd);">
        <h1 class="text-4xl font-bold mb-2">Senioren</h1>
        <p class="text-xl opacity-90">Unsere Seniorenmannschaft</p>
    </div>

    <div class="space-y-8">
        <!-- Team Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4 text-red-600">Mannschaftsinfo</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Trainer</h3>
                    <p class="text-gray-500">Wird geladen...</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Spielklasse</h3>
                    <p class="text-gray-500">Wird geladen...</p>
                </div>
            </div>
        </div>

        <!-- Matches Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: {{ $primaryColor }};">Kommende Spiele</h2>
            <div class="space-y-4">
                @forelse($matches as $match)
                <div class="border-b pb-4">
                    <div class="flex justify-between items-center gap-4">
                        <div class="flex-1">
                            <p class="text-sm text-gray-500 mb-2">{{ \Carbon\Carbon::parse($match->date_time_local)->format('d.m.Y H:i') }}</p>
                            <div class="flex items-center gap-4">
                                <!-- Home Team -->
                                <div class="flex items-center gap-2 flex-1">
                                    @if($match->team_logo_home)
                                        <img src="{{ $match->team_logo_home }}" alt="{{ $match->team_name_home }}" class="w-8 h-8 object-contain">
                                    @endif
                                    <span class="font-semibold text-gray-900">{{ $match->team_name_home }}</span>
                                </div>
                                
                                <!-- Score or VS -->
                                @if($match->team_score_home !== null && $match->team_score_away !== null)
                                    <div class="text-center px-4">
                                        <span class="text-lg font-bold" style="color: {{ $primaryColor }};">{{ $match->team_score_home }}:{{ $match->team_score_away }}</span>
                                    </div>
                                @else
                                    <div class="text-center px-4 text-gray-400 font-semibold">VS</div>
                                @endif
                                
                                <!-- Away Team -->
                                <div class="flex items-center gap-2 flex-1 justify-end">
                                    <span class="font-semibold text-gray-900">{{ $match->team_name_away }}</span>
                                    @if($match->team_logo_away)
                                        <img src="{{ $match->team_logo_away }}" alt="{{ $match->team_name_away }}" class="w-8 h-8 object-contain">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500">Keine Spiele verfügbar</p>
                @endforelse
            </div>
        </div>

        <!-- Table/Standings -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: {{ $primaryColor }};">Tabelle</h2>
            <div class="overflow-x-auto">
                @if($rankings->count() > 0)
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pos</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mannschaft</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sp</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">S</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">U</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">N</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tore</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pkt</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rankings as $rank)
                        <tr class="{{ $loop->iteration <= 3 ? 'bg-green-50' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $rank->position }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $rank->international_team_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $rank->matches_played }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $rank->wins }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $rank->draws }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $rank->losses }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $rank->goals_for }}:{{ $rank->goals_against }}</td>
                            <td class="px-4 py-3 text-sm font-bold text-center" style="color: {{ $primaryColor }};">{{ $rank->points }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-gray-500">Keine Tabellendaten verfügbar</p>
                @endif
            </div>
        </div>

        <!-- Top Scorers -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: {{ $primaryColor }};">Torschützenliste</h2>
            @if($topScorers->count() > 0)
            <div class="space-y-3">
                @foreach($topScorers as $scorer)
                <div class="flex justify-between items-center border-b pb-2">
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-600 font-medium">{{ $loop->iteration }}.</span>
                        <span class="font-medium">{{ $scorer->full_name }}</span>
                    </div>
                    <span class="font-bold" style="color: {{ $primaryColor }};">{{ $scorer->goals }} Tore</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500">Keine Torschützendaten verfügbar</p>
            @endif
        </div>
    </div>
</div>
@endsection
