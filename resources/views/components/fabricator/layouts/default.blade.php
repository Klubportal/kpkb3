@aware(['page'])
@php
    $settings = app(\App\Settings\GeneralSettings::class);
@endphp

<x-layouts.central :title="$page->title">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ $page->title }}</h1>

            @if($page->excerpt)
                <p class="text-xl text-gray-600 dark:text-gray-400">{{ $page->excerpt }}</p>
            @endif
        </div>

        {{-- Page Content Blocks --}}
        <div class="space-y-8">
            <x-filament-fabricator::page-blocks :blocks="$page->blocks" />
        </div>
    </div>
</x-layouts.central>
