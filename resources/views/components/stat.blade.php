@props([
    'icon' => null,
    'title' => '',
    'value' => '',
    'color' => 'primary'
])

<div class="stat bg-base-100 rounded-lg shadow-md hover-lift" x-data="counter({{ $value }})" x-intersect="animate">
    @if($icon)
    <div class="stat-figure text-{{ $color }}">
        {!! $icon !!}
    </div>
    @endif

    <div class="stat-title text-base-content/60">{{ $title }}</div>
    <div class="stat-value text-{{ $color }}" x-text="displayValue">0</div>

    @if($slot->isNotEmpty())
    <div class="stat-desc">
        {{ $slot }}
    </div>
    @endif
</div>
