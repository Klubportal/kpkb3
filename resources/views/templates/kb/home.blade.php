@extends('templates.kb.layout')

@section('title', 'Klubportal - Moderne Vereinsverwaltung')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                Deine Vereinswebsite.<br>
                <span class="text-blue-200">In 5 Minuten online.</span>
            </h1>
            <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                Professionelle Webpräsenz für Sportvereine. Ohne Programmierung. Ohne Stress. Mit allem was du brauchst.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('clubs.register') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition shadow-lg inline-flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Kostenlos registrieren
                </a>
                <a href="#features" class="bg-blue-500 bg-opacity-20 backdrop-blur text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-opacity-30 transition border border-blue-400 border-opacity-30 inline-flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                    Mehr erfahren
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div id="features" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Alles was dein Verein braucht
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Von Spielerstatistiken bis zur Mitgliederverwaltung - alles in einem System
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Spielerverwaltung</h3>
                <p class="text-gray-600">
                    Alle Spielerdaten, Statistiken und Leistungen automatisch synchronisiert über Comet API
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Live Statistiken</h3>
                <p class="text-gray-600">
                    Echtzeit-Spielstatistiken, Tabellen und Torschützenlisten - immer aktuell
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Eigenes Design</h3>
                <p class="text-gray-600">
                    Wähle aus professionellen Templates und passe Farben an deine Vereinsfarben an
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">News & Artikel</h3>
                <p class="text-gray-600">
                    Veröffentliche News, Spielberichte und halte deine Fans auf dem Laufenden
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Spielplan</h3>
                <p class="text-gray-600">
                    Automatischer Spielplan mit allen kommenden und vergangenen Spielen
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Einfache Verwaltung</h3>
                <p class="text-gray-600">
                    Intuitives Admin-Panel zum Verwalten aller Inhalte - keine technischen Kenntnisse nötig
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Templates Section -->
<div id="templates" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Wähle dein Design
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Moderne, responsive Templates - perfekt für deinen Verein
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Template KB -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                <div class="h-48 bg-gradient-to-br from-blue-500 to-indigo-600"></div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">KB Template</h3>
                    <p class="text-gray-600 mb-4">Modern und clean. Perfekt für professionelle Vereinsauftritte.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Aktuell ausgewählt</span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Standard</span>
                    </div>
                </div>
            </div>

            <!-- Template BM -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                <div class="h-48 bg-gradient-to-br from-red-500 to-pink-600"></div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">BM Template</h3>
                    <p class="text-gray-600 mb-4">Sportlich und dynamisch. Mit großen Bildern und klaren Strukturen.</p>
                    <div class="flex items-center justify-between">
                        <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">Vorschau ansehen</a>
                        <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">Verfügbar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-blue-600 text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
            Bereit loszulegen?
        </h2>
        <p class="text-xl text-blue-100 mb-8">
            Erstelle jetzt kostenlos die Website für deinen Verein
        </p>
        <a href="{{ route('clubs.register') }}" class="bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition shadow-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Jetzt kostenlos registrieren
        </a>
    </div>
</div>
@endsection
