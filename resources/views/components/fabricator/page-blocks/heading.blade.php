@aware(['page'])

<div class="my-6">
    @if($level === 'h1')
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">{{ $content }}</h1>
    @elseif($level === 'h2')
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $content }}</h2>
    @elseif($level === 'h3')
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $content }}</h3>
    @else
        <h4 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $content }}</h4>
    @endif
</div>
