@props([
    'title' => null,
    'image' => null,
    'date' => null,
    'category' => null,
    'url' => '#',
    'excerpt' => null,
    'featured' => false,
    'animate' => true
])

<div class="card bg-base-100 shadow-xl {{ $animate ? 'hover-lift' : '' }} {{ $featured ? 'lg:col-span-2' : '' }}">
    @if($image)
    <figure class="{{ $featured ? 'h-96' : 'h-48' }} overflow-hidden image-zoom">
        <img src="{{ $image }}"
             alt="{{ $title }}"
             class="w-full h-full object-cover">
    </figure>
    @else
    <figure class="{{ $featured ? 'h-96' : 'h-48' }} bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-20 h-20 text-base-content/20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
        </svg>
    </figure>
    @endif

    <div class="card-body {{ $featured ? 'p-8' : '' }}">
        {{-- Meta Info --}}
        <div class="flex items-center gap-3 text-sm text-base-content/60 mb-2 flex-wrap">
            @if($date)
            <div class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                {{ $date }}
            </div>
            @endif

            @if($category)
            <div class="badge badge-primary badge-sm">{{ $category }}</div>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="card-title {{ $featured ? 'text-3xl' : 'text-xl' }} line-clamp-2">
            {{ $title }}
        </h3>

        {{-- Excerpt --}}
        @if($excerpt)
        <p class="text-base-content/70 {{ $featured ? 'text-lg line-clamp-4' : 'line-clamp-3' }}">
            {{ $excerpt }}
        </p>
        @endif

        {{-- Custom Content --}}
        @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
        @endif

        {{-- Action --}}
        <div class="card-actions justify-end mt-4">
            <a href="{{ $url }}" class="btn btn-primary btn-sm">
                Weiterlesen →
            </a>
        </div>
    </div>
</div>
