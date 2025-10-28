<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sync Dashboard - Central Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-sync-alt mr-2"></i> Comet Sync Dashboard
                    </h1>
                    <div class="flex items-center space-x-4">
                        <span id="sync-status" class="text-sm text-gray-600"></span>
                        <a href="/admin" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-arrow-left mr-1"></i> Zurück zum Admin
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <i class="fas fa-calendar-day text-white text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Syncs heute
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        {{ $stats['total_syncs_today'] }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <i class="fas fa-check-circle text-white text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Erfolgreich
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        {{ $stats['successful_syncs_today'] }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Fehlgeschlagen
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        {{ $stats['failed_syncs_today'] }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <i class="fas fa-database text-white text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Records 24h
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        {{ number_format($stats['last_24h_records']) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Sync Triggers -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-hand-pointer mr-2"></i> Manuelle Synchronisation
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Sync All Button -->
                        <button onclick="triggerSync('all')"
                                class="sync-btn bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-4 px-6 rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Alle Daten syncen
                        </button>

                        <!-- Sync Matches Button -->
                        <button onclick="triggerSync('matches')"
                                class="sync-btn bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-4 px-6 rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                            <i class="fas fa-futbol mr-2"></i>
                            Matches syncen
                        </button>

                        <!-- Sync Rankings Button -->
                        <button onclick="triggerSync('rankings')"
                                class="sync-btn bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-4 px-6 rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                            <i class="fas fa-trophy mr-2"></i>
                            Rankings syncen
                        </button>

                        <!-- Sync Top Scorers Button -->
                        <button onclick="triggerSync('topscorers')"
                                class="sync-btn bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-4 px-6 rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                            <i class="fas fa-star mr-2"></i>
                            Top Scorers syncen
                        </button>

                        <!-- Sync Tenants Button -->
                        <button onclick="triggerSync('tenants')"
                                class="sync-btn bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                            <i class="fas fa-users mr-2"></i>
                            Tenants syncen
                        </button>

                        <!-- Refresh Status Button -->
                        <button onclick="loadRecentSyncs()"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-4 px-6 rounded-lg shadow transition duration-200">
                            <i class="fas fa-redo mr-2"></i>
                            Status aktualisieren
                        </button>
                    </div>

                    <!-- Sync Output -->
                    <div id="sync-output" class="mt-6 hidden">
                        <div class="bg-gray-900 text-green-400 rounded-lg p-4 font-mono text-sm overflow-auto max-h-96">
                            <div id="sync-output-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Syncs -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-history mr-2"></i> Letzte Synchronisationen
                        </h2>
                        <a href="{{ route('admin.sync.history') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            Alle anzeigen <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Typ
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Eingefügt
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aktualisiert
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dauer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Gestartet
                                </th>
                            </tr>
                        </thead>
                        <tbody id="sync-table-body" class="bg-white divide-y divide-gray-200">
                            @foreach($recentSyncs as $sync)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $sync->sync_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($sync->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Erfolgreich
                                        </span>
                                    @elseif($sync->status === 'running')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Läuft
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Fehler
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($sync->records_inserted ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($sync->records_updated ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sync->duration_seconds ? round($sync->duration_seconds, 2) . 's' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $sync->started_at->diffForHumans() }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-refresh status every 5 seconds
        setInterval(updateSyncStatus, 5000);

        // Initial load
        updateSyncStatus();

        function updateSyncStatus() {
            fetch('/admin/sync/status')
                .then(res => res.json())
                .then(data => {
                    const statusEl = document.getElementById('sync-status');
                    if (data.is_syncing) {
                        statusEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sync läuft...';
                        statusEl.className = 'text-sm text-yellow-600 font-semibold';
                    } else if (data.last_sync) {
                        const lastSync = new Date(data.last_sync.started_at);
                        statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Letzter Sync: ' + lastSync.toLocaleString('de-DE');
                        statusEl.className = 'text-sm text-green-600';
                    }
                })
                .catch(err => console.error('Status update failed:', err));
        }

        function triggerSync(type) {
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;

            // Disable button and show loading
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Wird ausgeführt...';

            // Show output container
            const outputDiv = document.getElementById('sync-output');
            const outputContent = document.getElementById('sync-output-content');
            outputDiv.classList.remove('hidden');
            outputContent.innerHTML = `<div class="text-yellow-400">⏳ Starte ${type} Sync...</div>`;

            // Make request
            fetch(`/admin/sync/${type}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ async: false })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    outputContent.innerHTML = `<div class="text-green-400">✅ ${data.message}</div>`;
                    if (data.output) {
                        outputContent.innerHTML += `<pre class="mt-2 text-gray-300">${data.output}</pre>`;
                    }

                    // Reload table after 2 seconds
                    setTimeout(loadRecentSyncs, 2000);
                } else {
                    outputContent.innerHTML = `<div class="text-red-400">❌ ${data.message}</div>`;
                }
            })
            .catch(err => {
                outputContent.innerHTML = `<div class="text-red-400">❌ Fehler: ${err.message}</div>`;
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalContent;
            });
        }

        function loadRecentSyncs() {
            fetch('/admin/sync/history?limit=10')
                .then(res => res.json())
                .then(data => {
                    // Update table (simplified for now)
                    location.reload();
                })
                .catch(err => console.error('Failed to load recent syncs:', err));
        }
    </script>
</body>
</html>
