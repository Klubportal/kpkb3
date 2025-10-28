<div>
    @php
        $record = $getRecord();
        $logoPath = $record?->logo;
    @endphp

    @if($logoPath)
        <div class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-4">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <img
                        src="/storage/{{ $logoPath }}"
                        alt="Aktuelles Logo"
                        class="h-24 w-auto max-w-[200px] rounded border border-gray-200 dark:border-gray-700 object-contain bg-gray-50 dark:bg-gray-900 p-2"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                    >
                    <div style="display:none;" class="text-sm text-gray-500">
                        Bild konnte nicht geladen werden
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Aktuelles Logo
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                        {{ basename($logoPath) }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        Pfad: {{ $logoPath }}
                    </p>
                    <div class="mt-3">
                        <a
                            href="/storage/{{ $logoPath }}"
                            target="_blank"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
                        >
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            In voller Größe ansehen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Kein Logo vorhanden
        </div>
    @endif
</div>

