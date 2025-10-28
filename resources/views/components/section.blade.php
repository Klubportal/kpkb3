@props([
    'title' => '',
    'subtitle' => null,
    'background' => 'bg-base-200',
    'centered' => false,
    'maxWidth' => 'max-w-7xl',
    'padding' => 'py-16 px-4'
])

<section class="{{ $background }} {{ $padding }}">
    <div class="{{ $maxWidth }} mx-auto">
        @if($title || $subtitle)
        <div class="{{ $centered ? 'text-center' : '' }} mb-12">
            @if($subtitle)
            <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-2 animate-fadeIn">
                {{ $subtitle }}
            </p>
            @endif

            @if($title)
            <h2 class="text-4xl md:text-5xl font-bold text-base-content animate-fadeInUp">
                {{ $title }}
            </h2>
            @endif
        </div>
        @endif

        {{ $slot }}
    </div>
</section>
