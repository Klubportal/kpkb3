<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CometSyncController extends Controller
{
    /**
     * Show sync dashboard with status and history
     */
    public function index()
    {
        // Get recent sync logs
        $recentSyncs = SyncLog::orderBy('started_at', 'desc')
            ->take(20)
            ->get();

        // Get latest sync for each type
        $latestSyncs = SyncLog::select('sync_type')
            ->selectRaw('MAX(started_at) as last_sync')
            ->groupBy('sync_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->sync_type => $item->last_sync];
            });

        // Get sync statistics
        $stats = [
            'total_syncs_today' => SyncLog::whereDate('started_at', today())->count(),
            'successful_syncs_today' => SyncLog::whereDate('started_at', today())
                ->where('status', 'completed')
                ->count(),
            'failed_syncs_today' => SyncLog::whereDate('started_at', today())
                ->where('status', 'failed')
                ->count(),
            'last_24h_records' => SyncLog::where('started_at', '>=', now()->subDay())
                ->sum('records_processed'),
        ];

        return view('admin.sync.index', compact('recentSyncs', 'latestSyncs', 'stats'));
    }

    /**
     * Trigger match sync manually
     */
    public function syncMatches(Request $request)
    {
        try {
            // Run sync in background or foreground based on request
            if ($request->boolean('async', false)) {
                Artisan::queue('comet:sync-matches');
                return response()->json([
                    'success' => true,
                    'message' => 'Match sync started in background',
                    'async' => true,
                ]);
            }

            // Run synchronously
            Artisan::call('comet:sync-matches');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Match sync completed successfully',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Manual match sync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger ranking sync manually
     */
    public function syncRankings(Request $request)
    {
        try {
            if ($request->boolean('async', false)) {
                Artisan::queue('comet:sync-rankings');
                return response()->json([
                    'success' => true,
                    'message' => 'Ranking sync started in background',
                    'async' => true,
                ]);
            }

            Artisan::call('comet:sync-rankings');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Ranking sync completed successfully',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Manual ranking sync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger top scorers sync manually
     */
    public function syncTopScorers(Request $request)
    {
        try {
            if ($request->boolean('async', false)) {
                Artisan::queue('comet:sync-topscorers');
                return response()->json([
                    'success' => true,
                    'message' => 'Top scorers sync started in background',
                    'async' => true,
                ]);
            }

            Artisan::call('comet:sync-topscorers');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Top scorers sync completed successfully',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Manual top scorers sync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger full sync (all data types)
     */
    public function syncAll(Request $request)
    {
        try {
            if ($request->boolean('async', false)) {
                Artisan::queue('comet:sync-all');
                return response()->json([
                    'success' => true,
                    'message' => 'Full sync started in background',
                    'async' => true,
                ]);
            }

            Artisan::call('comet:sync-all');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Full sync completed successfully',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Manual full sync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger tenant data sync
     */
    public function syncTenants(Request $request)
    {
        try {
            $tenantId = $request->input('tenant_id');

            if ($tenantId) {
                // Sync specific tenant
                $command = "tenant:sync-comet {$tenantId}";
            } else {
                // Sync all tenants
                $command = 'tenant:sync-comet --all';
            }

            if ($request->boolean('async', false)) {
                Artisan::queue($command);
                return response()->json([
                    'success' => true,
                    'message' => 'Tenant sync started in background',
                    'async' => true,
                ]);
            }

            Artisan::call($command);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Tenant sync completed successfully',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Manual tenant sync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show sync history
     */
    public function history(Request $request)
    {
        $query = SyncLog::orderBy('started_at', 'desc');

        // Filter by sync type if specified
        if ($request->has('type')) {
            $query->where('sync_type', $request->input('type'));
        }

        // Filter by status if specified
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('started_at', '>=', $request->input('from'));
        }
        if ($request->has('to')) {
            $query->whereDate('started_at', '<=', $request->input('to'));
        }

        $syncs = $query->paginate(50);

        if ($request->wantsJson()) {
            return response()->json($syncs);
        }

        return view('admin.sync.history', compact('syncs'));
    }

    /**
     * Show details of a specific sync log
     */
    public function show($id)
    {
        $syncLog = SyncLog::findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json($syncLog);
        }

        return view('admin.sync.show', compact('syncLog'));
    }

    /**
     * Get sync status (for AJAX polling)
     */
    public function status()
    {
        $runningSyncs = SyncLog::where('status', 'running')
            ->where('started_at', '>=', now()->subHours(2))
            ->get();

        $lastSync = SyncLog::orderBy('started_at', 'desc')->first();

        return response()->json([
            'running_syncs' => $runningSyncs,
            'last_sync' => $lastSync,
            'is_syncing' => $runningSyncs->isNotEmpty(),
        ]);
    }
}
