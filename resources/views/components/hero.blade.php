@props([
    'title' => '',
    'subtitle' => '',
    'image' => null,
    'height' => 'h-[600px]',
    'gradient' => 'from-primary to-secondary',
    'cta' => null,
    'ctaUrl' => '#',
    'ctaStyle' => 'btn-primary'
])

<section class="relative {{ $height }} bg-gradient-to-r {{ $gradient }} overflow-hidden">
    {{-- Background Image --}}
    @if($image)
    <div class="absolute inset-0">
        <img src="{{ $image }}"
             alt="{{ $title }}"
             class="w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
    </div>
    @endif

    {{-- Content --}}
    <div class="relative max-w-7xl mx-auto px-4 h-full flex items-center">
        <div class="max-w-3xl text-white animate-fadeInUp">
            @if($subtitle)
            <div class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold mb-4 animate-fadeIn">
                {{ $subtitle }}
            </div>
            @endif

            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
                {{ $title }}
            </h1>

            @if($slot->isNotEmpty())
            <div class="text-xl md:text-2xl mb-8 text-white/90">
                {{ $slot }}
            </div>
            @endif

            @if($cta)
            <a href="{{ $ctaUrl }}"
               class="btn {{ $ctaStyle }} btn-lg gap-2 animate-scaleIn">
                {{ $cta }}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
            @endif
        </div>
    </div>

    {{-- Scroll Down Indicator --}}
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-float">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-white">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </div>
</section>
