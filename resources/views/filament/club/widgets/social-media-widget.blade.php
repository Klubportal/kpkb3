<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📱 Social Media Feed
        </x-slot>

        <div class="space-y-3">
            @foreach($this->getSocialPosts() as $post)
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-start gap-3">
                        <div class="text-2xl">{{ $post['icon'] }}</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $post['author'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $post['time'] }}</p>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ $post['content'] }}</p>
                            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                                    </svg>
                                    {{ $post['likes'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 grid grid-cols-3 gap-2">
            <a href="#" class="flex items-center justify-center gap-2 p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30 transition text-sm">
                <span>𝕏</span> Twitter
            </a>
            <a href="#" class="flex items-center justify-center gap-2 p-2 bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400 rounded hover:bg-pink-100 dark:hover:bg-pink-900/30 transition text-sm">
                <span>📷</span> Instagram
            </a>
            <a href="#" class="flex items-center justify-center gap-2 p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30 transition text-sm">
                <span>👍</span> Facebook
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
