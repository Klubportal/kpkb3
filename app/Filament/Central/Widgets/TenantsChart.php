<?php

namespace App\Filament\Central\Widgets;

use App\Models\Central\Tenant;

// Provide safe fallback if ApexCharts plugin is not available
if (class_exists(\Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget::class)) {
    class TenantsChart extends \Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget
    {
    protected static ?string $chartId = 'tenantsChart';

    protected static ?int $sort = 2;

    protected function getHeading(): ?string
    {
        return __('Tenant Growth');
    }

    protected function getOptions(): array
    {
        // Last 12 months data
        $months = collect(range(11, 0))->map(function ($month) {
            return now()->subMonths($month);
        });

        $data = $months->map(function ($date) {
            return Tenant::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        });

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => __('New Tenants'),
                    'data' => $data->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $months->map(fn($date) => $date->format('M Y'))->toArray(),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#3b82f6'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.3,
                ],
            ],
        ];
    }
    }
} else {
    class TenantsChart
    {
        // No-op fallback
    }
}
