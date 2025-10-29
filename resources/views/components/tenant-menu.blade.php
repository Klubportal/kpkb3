<nav class="bg-white dark:bg-gray-800 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            {{-- Logo --}}
            <div class="flex items-center">
                <a href="/" class="flex items-center">
                    @if($logo = tenant()?->getLogo())
                        <img src="{{ $logo }}" alt="Logo" class="h-10 w-auto">
                    @else
                        <span class="text-xl font-bold" style="color: {{ $primaryColor }}">
                            {{ tenant()?->name ?? config('app.name') }}
                        </span>
                    @endif
                </a>
            </div>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex md:items-center md:space-x-1">
                @foreach(\App\Services\MenuService::getCachedMenu() as $item)
                    @if(isset($item['children']))
                        {{-- Dropdown Menu --}}
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button
                                @click="open = !open"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors
                                    {{ request()->is($item['active'] ?? '') ? 'text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                style="{{ request()->is($item['active'] ?? '') ? 'background-color: ' . $primaryColor : '' }}">
                                @if(isset($item['icon']))
                                    <x-icon name="{{ $item['icon'] }}" class="h-5 w-5 mr-1" />
                                @endif
                                {{ $item['label'] }}
                                <svg class="ml-1 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition
                                 class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
                                <div class="py-1">
                                    @foreach($item['children'] as $child)
                                        <a href="{{ $child['url'] }}"
                                           class="block px-4 py-2 text-sm {{ request()->is($child['active'] ?? '') ? 'font-semibold' : '' }} text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                                           style="{{ request()->is($child['active'] ?? '') ? 'color: ' . $primaryColor : '' }}">
                                            {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Single Menu Item --}}
                        <a href="{{ $item['url'] }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors
                                {{ request()->is($item['active'] ?? '') ? 'text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                           style="{{ request()->is($item['active'] ?? '') ? 'background-color: ' . $primaryColor : '' }}">
                            @if(isset($item['icon']))
                                <x-icon name="{{ $item['icon'] }}" class="h-5 w-5 mr-1" />
                            @endif
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>

            {{-- Mobile Menu Button --}}
            <div class="flex items-center md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen"
         x-transition
         class="md:hidden bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
        <div class="px-2 pt-2 pb-3 space-y-1">
            @foreach(\App\Services\MenuService::getCachedMenu() as $item)
                @if(isset($item['children']))
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 text-base font-medium rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span>{{ $item['label'] }}</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" class="pl-6 space-y-1">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['url'] }}"
                                   class="block px-3 py-2 text-base font-medium rounded-md {{ request()->is($child['active'] ?? '') ? 'font-semibold' : '' }} text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                                   style="{{ request()->is($child['active'] ?? '') ? 'color: ' . $primaryColor : '' }}">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $item['url'] }}"
                       class="block px-3 py-2 text-base font-medium rounded-md {{ request()->is($item['active'] ?? '') ? 'text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                       style="{{ request()->is($item['active'] ?? '') ? 'background-color: ' . $primaryColor : '' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</nav>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('menu', () => ({
            mobileMenuOpen: false
        }))
    })
</script>
@endpush
