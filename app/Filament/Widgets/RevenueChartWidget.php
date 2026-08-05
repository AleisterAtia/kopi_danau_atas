<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Tren Pendapatan';

    protected static ?string $description = 'Pendapatan harian dalam 30 hari terakhir';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        $data = Cache::remember('admin.chart.revenue', 60, function () use ($days) {
            $rows = Payment::where('status', 'settlement')
                ->whereBetween('paid_at', [$days->first()->startOfDay(), $days->last()->endOfDay()])
                ->selectRaw('DATE(paid_at) as date, SUM(gross_amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            return $days->map(fn ($d) => (int) ($rows[$d->toDateString()] ?? 0));
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $data->values(),
                    'borderColor' => '#0d9488',
                    'backgroundColor' => 'rgba(13, 148, 136, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2.5,
                ],
            ],
            'labels' => $days->map(fn ($d) => $d->translatedFormat('d M')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
