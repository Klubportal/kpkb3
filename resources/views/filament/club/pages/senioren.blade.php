<x-filament-panels::page>
    {{-- Filter-Section --}}
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Wettbewerb
                </label>
                <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option>Alle Wettbewerbe</option>
                    <option>Bundesliga Senioren</option>
                    <option>Pokal</option>
                    <option>Freundschaftsspiele</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Saison
                </label>
                <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option>2024/25</option>
                    <option>2023/24</option>
                    <option>2022/23</option>
                </select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Von
                </label>
                <input type="date" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Bis
                </label>
                <input type="date" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div>
                <button type="button" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    Filtern
                </button>
            </div>

            <div>
                <button type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Zurücksetzen
                </button>
            </div>
        </div>
    </div>

    {{-- Quick Stats Tabs --}}
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="#" class="border-primary-500 text-primary-600 dark:text-primary-400 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Übersicht
                </a>
                <a href="#" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Spielplan
                </a>
                <a href="#" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Kader
                </a>
                <a href="#" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Statistiken
                </a>
                <a href="#" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Galerie
                </a>
            </nav>
        </div>
    </div>

    {{-- Widgets werden automatisch von Filament gerendert --}}
</x-filament-panels::page>
