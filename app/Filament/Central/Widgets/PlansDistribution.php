<?php

namespace App\Filament\Central\Widgets;

use App\Models\Central\Plan;

// Provide safe fallback if ApexCharts plugin is not available
if (class_exists(\Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget::class)) {
    class PlansDistribution extends \Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget
    {
    protected static ?string $chartId = 'plansDistribution';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getHeading(): ?string
    {
        return __('Subscription Plans');
    }

    protected function getOptions(): array
    {
        $plans = Plan::withCount('tenants')->get();

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $plans->pluck('tenants_count')->toArray(),
            'labels' => $plans->pluck('name')->toArray(),
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'colors' => ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981'],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'labels' => [
                            'show' => true,
                            'total' => [
                                'show' => true,
                                'label' => __('Total Tenants'),
                                'fontFamily' => 'inherit',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
    }
} else {
    class PlansDistribution
    {
        // No-op fallback
    }
}
