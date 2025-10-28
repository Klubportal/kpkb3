<a href="https://www.klubportal.com" target="_blank" rel="noopener noreferrer" class="flex items-center">
    @if ($logo)
        <img src="{{ $logo }}" alt="{{ $brandName }}" style="height: {{ $logoHeight }};" class="dark:hidden">
    @else
        <span class="text-xl font-bold">{{ $brandName }}</span>
    @endif
</a>
