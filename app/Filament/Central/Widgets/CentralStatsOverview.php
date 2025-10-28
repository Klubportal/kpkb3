<?php

namespace App\Filament\Central\Widgets;

use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CentralStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $tenantsTrial = Tenant::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();
        // Use trial_ends_at as proxy for expiring subscriptions
        $tenantsExpiringSoon = Tenant::whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(30)])
            ->count();

        return [
            Stat::make(__('Total Tenants'), $totalTenants)
                ->description(__('Registered clubs'))
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary')
                ->chart([7, 12, 15, 18, 22, 25, $totalTenants]),

            Stat::make(__('Active Tenants'), $activeTenants)
                ->description(__('Currently active'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart([5, 10, 12, 15, 18, 20, $activeTenants]),

            Stat::make(__('Trial Accounts'), $tenantsTrial)
                ->description(__('In trial period'))
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make(__('Trials Expiring Soon'), $tenantsExpiringSoon)
                ->description(__('Next 30 days'))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
