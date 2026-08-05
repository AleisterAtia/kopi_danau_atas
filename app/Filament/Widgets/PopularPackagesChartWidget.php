<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class PopularPackagesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Paket Wisata Terpopuler';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $rows = Cache::remember('admin.chart.popular_packages', 60, function () {
            return Booking::query()
                ->whereIn('status', ['paid', 'confirmed', 'completed'])
                ->with('tourPackage:id,name')
                ->selectRaw('tour_package_id, SUM(guest_count) as total_guests')
                ->groupBy('tour_package_id')
                ->orderByDesc('total_guests')
                ->limit(5)
                ->get();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Wisatawan',
                    'data' => $rows->pluck('total_guests'),
                    'backgroundColor' => '#0d9488',
                    'borderRadius' => 6,
                    'maxBarThickness' => 40,
                ],
            ],
            'labels' => $rows->map(fn ($row) => $row->tourPackage?->name ?? '—'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['borderDash' => [4, 4]],
                    'border' => ['display' => false],
                ],
            ],
        ];
    }
}
