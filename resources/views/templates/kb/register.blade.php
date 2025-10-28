@extends('templates.kb.layout')

@section('title', 'Verein registrieren - Klubportal')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Verein registrieren
            </h1>
            <p class="text-lg text-gray-600">
                In wenigen Schritten zu deiner professionellen Vereinswebsite
            </p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            @livewire('club-registration-form')
        </div>

        <!-- Benefits -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-900">Kostenlos starten</h3>
                    <p class="text-sm text-gray-500">Keine versteckten Kosten</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-900">Sofort online</h3>
                    <p class="text-sm text-gray-500">Nach Freischaltung direkt nutzbar</p>
                </div>
            </div>

            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-900">Support inklusive</h3>
                    <p class="text-sm text-gray-500">Wir helfen dir beim Start</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
