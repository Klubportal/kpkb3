@extends('central-frontend.layout')

@section('title', 'Klubportal - Vereinsverwaltung für Fußballvereine')

@section('hero')
<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Vereinsverwaltung leicht gemacht</h1>
        <p class="text-xl mb-8 text-blue-100">Moderne Lösung für Fußballvereine - Spielerverwaltung, Spielpläne, Statistiken und mehr</p>
        <div class="flex justify-center space-x-4">
            <a href="#vereine" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">Vereine entdecken</a>
            <a href="/tenant-registration" class="bg-blue-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-400 transition">Verein anmelden</a>
        </div>
    </div>
</section>
@endsection

@section('content')
<!-- Features Section -->
<section id="features" class="py-16">
    <h2 class="text-4xl font-bold text-center mb-12 text-gray-800">Features</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Spielerverwaltung</h3>
            <p class="text-gray-600">Komplette Verwaltung von Spielerdaten, Kontakten, medizinischen Informationen und mehr</p>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Spielpläne & Ergebnisse</h3>
            <p class="text-gray-600">COMET-Integration für automatische Spielpläne, Live-Ergebnisse und Tabellen</p>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Statistiken & Analysen</h3>
            <p class="text-gray-600">Umfassende Statistiken zu Spielern, Mannschaften und Wettbewerben</p>
        </div>
    </div>
</section>

<!-- Vereine Section -->
<section id="vereine" class="py-16 bg-white rounded-2xl shadow-lg">
    <h2 class="text-4xl font-bold text-center mb-12 text-gray-800">Unsere Vereine</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($tenants as $tenant)
        <a href="http://{{ $tenant->domains->first()->domain }}" class="block bg-gradient-to-br from-gray-50 to-white p-6 rounded-xl border-2 border-gray-200 hover:border-blue-500 hover:shadow-xl transition">
            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $tenant->name }}</h3>
            <p class="text-sm text-gray-600 mb-4">{{ $tenant->domains->first()->domain }}</p>
            <span class="text-blue-600 font-semibold flex items-center">
                Website besuchen
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        </a>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500 text-lg">Noch keine Vereine registriert</p>
        </div>
        @endforelse
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 text-center">
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl shadow-xl p-12 text-white">
        <h2 class="text-3xl font-bold mb-4">Bereit für deinen Verein?</h2>
        <p class="text-xl mb-8 text-blue-100">Starte jetzt mit der modernen Vereinsverwaltung</p>
        <a href="/tenant-registration" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
            Jetzt kostenlos anmelden
        </a>
    </div>
</section>
@endsection
