<x-filament-panels::page.simple>
    @php
        $settings = \DB::connection('central')
            ->table('settings')
            ->where('group', 'general')
            ->pluck('payload', 'name')
            ->map(fn($value) => json_decode($value, true));

        $publicBase = config('filesystems.disks.public.url');
        $logo = isset($settings['logo']) && $settings['logo']
            ? rtrim($publicBase, '/').'/'.ltrim($settings['logo'], '/')
            : null;
        $siteName = $settings['site_name'] ?? 'Klubportal Central';
    @endphp

    {{-- Modern Gradient Background --}}
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-blue-900 dark:to-indigo-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            {{-- Card with Glass Effect --}}
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
                
                {{-- Logo Section --}}
                <div class="text-center mb-8">
                    @if($logo)
                    <div class="flex justify-center" style="margin-bottom: 15px;">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full blur-2xl opacity-20 animate-pulse"></div>
                            <img src="{{ $logo }}" alt="{{ $siteName }}" class="relative h-40 w-auto object-contain drop-shadow-2xl">
                        </div>
                    </div>
                    @endif

                    <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                        {{ $siteName }}
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Prijavite se u svoj račun
                    </p>
                </div>

                {{-- Login Form --}}
                <div class="space-y-6">
                    <x-filament-panels::form wire:submit="authenticate">
                        {{ $this->form }}

                        <x-filament-panels::form.actions
                            :actions="$this->getCachedFormActions()"
                            :full-width="$this->hasFullWidthFormActions()"
                        />
                    </x-filament-panels::form>
                </div>

                {{-- Footer --}}
                <div class="mt-8 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        © {{ date('Y') }} {{ $siteName }}. All rights reserved.
                    </p>
                </div>
            </div>

            {{-- Decorative Elements --}}
            <div class="absolute top-0 left-0 w-full h-full pointer-events-none overflow-hidden -z-10">
                <div class="absolute top-1/4 -left-20 w-72 h-72 bg-blue-300 dark:bg-blue-700 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-3xl opacity-30 animate-blob"></div>
                <div class="absolute top-1/3 -right-20 w-72 h-72 bg-purple-300 dark:bg-purple-700 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-1/3 w-72 h-72 bg-pink-300 dark:bg-pink-700 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0%, 100% {
                transform: translate(0px, 0px) scale(1);
            }
            33% {
                transform: translate(30px, -50px) scale(1.1);
            }
            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
</x-filament-panels::page.simple>
