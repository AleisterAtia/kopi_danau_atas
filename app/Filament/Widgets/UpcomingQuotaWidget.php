<?php

namespace App\Filament\Widgets;

use App\Models\TourPackage;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

/**
 * Shows quota usage for the next 7 days, per active tour package.
 * Each row is one (package × date) pair. The booked + available numbers
 * use the same logic as the public site (TourPackage::getBookedCount).
 */
class UpcomingQuotaWidget extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-quota';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 3;

    protected function getViewData(): array
    {
        $packages = TourPackage::where('is_active', true)->orderBy('name')->get();
        $dates = collect(range(0, 6))->map(fn ($i) => Carbon::today()->addDays($i));

        $rows = [];
        foreach ($dates as $date) {
            foreach ($packages as $package) {
                $booked = $package->getBookedCount($date->toDateString());
                $available = max(0, $package->daily_capacity - $booked);
                $utilization = $package->daily_capacity > 0
                    ? (int) round(($booked / $package->daily_capacity) * 100)
                    : 0;

                $rows[] = [
                    'date' => $date,
                    'package_name' => $package->name,
                    'capacity' => $package->daily_capacity,
                    'booked' => $booked,
                    'available' => $available,
                    'utilization' => $utilization,
                ];
            }
        }

        return [
            'rows' => $rows,
            'hasPackages' => $packages->isNotEmpty(),
        ];
    }
}
