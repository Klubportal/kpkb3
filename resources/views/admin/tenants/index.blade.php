<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Verwaltung - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">🏢 Tenant Verwaltung</h1>
                    <a href="{{ route('admin.tenants.create') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        ➕ Neuen Tenant erstellen
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                ✅ {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                ❌ {{ session('error') }}
            </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Total Tenants</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $tenants->count() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Mit COMET Sync</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">
                        {{ $tenants->filter(fn($t) => $t->comet_stats)->count() }}
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500">Ohne COMET</div>
                    <div class="mt-2 text-3xl font-bold text-gray-400">
                        {{ $tenants->filter(fn($t) => !$t->comet_stats)->count() }}
                    </div>
                </div>
            </div>

            <!-- Tenants Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tenant
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Domain
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                FIFA ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                COMET Stats
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Erstellt
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aktionen
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $tenant->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ $tenant->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $tenant->domains->first()?->domain ?? 'Keine Domain' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(isset($tenant->data['club_fifa_id']))
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                                    {{ $tenant->data['club_fifa_id'] }}
                                </span>
                                @else
                                <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tenant->comet_stats)
                                <div class="text-xs space-y-1">
                                    <div>🏆 {{ $tenant->comet_stats['competitions'] }} Competitions</div>
                                    <div>⚽ {{ $tenant->comet_stats['matches'] }} Matches</div>
                                    <div>📊 {{ $tenant->comet_stats['rankings'] }} Rankings</div>
                                </div>
                                @else
                                <span class="text-sm text-gray-400">Kein COMET Sync</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $tenant->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="http://{{ $tenant->domains->first()?->domain }}"
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    Öffnen
                                </a>
                                <a href="#" class="text-gray-600 hover:text-gray-900">
                                    Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="text-4xl mb-4">📋</div>
                                <div class="text-lg font-medium">Keine Tenants vorhanden</div>
                                <div class="mt-2">
                                    <a href="{{ route('admin.tenants.create') }}" class="text-blue-600 hover:text-blue-800">
                                        Erstellen Sie den ersten Tenant
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Back Link -->
            <div class="mt-6">
                <a href="/admin" class="text-blue-600 hover:text-blue-800">
                    ← Zurück zum Admin Dashboard
                </a>
            </div>
        </main>
    </div>
</body>
</html>
