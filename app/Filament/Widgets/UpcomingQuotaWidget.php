<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\TourPackage;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Shows quota usage for the next 7 days, per active tour package.
 * Each row is one (package × date) pair.
 *
 * Performance note: a previous implementation called
 * TourPackage::getBookedCount() inside a nested loop, which produced
 * N×M aggregate queries (≈ 7 days × packages). This version computes the
 * booked counts using a single grouped aggregate query and joins them in
 * memory.
 */
class UpcomingQuotaWidget extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-quota';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected function getViewData(): array
    {
        $packages = TourPackage::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'daily_capacity']);

        $dates = collect(range(0, 6))->map(fn ($i) => Carbon::today()->addDays($i));
        $dateStrings = $dates->map(fn ($d) => $d->toDateString())->all();

        // Single grouped aggregate replaces the previous N×M loop. Mirrors the
        // logic in TourPackage::getBookedCount(): paid/confirmed/completed
        // always count, plus pending bookings created within the last hour
        // (older pending bookings are auto-expired by a scheduled command).
        $bookedMap = [];
        if ($packages->isNotEmpty() && ! empty($dateStrings)) {
            $rows = Booking::query()
                ->whereIn('tour_package_id', $packages->pluck('id'))
                ->whereIn('visit_date', $dateStrings)
                ->where(function ($query) {
                    $query->whereIn('status', ['paid', 'confirmed', 'completed'])
                        ->orWhere(function ($q) {
                            $q->where('status', 'pending')
                                ->where('created_at', '>=', now()->subHour());
                        });
                })
                ->groupBy('tour_package_id', 'visit_date')
                ->select([
                    'tour_package_id',
                    'visit_date',
                    DB::raw('SUM(guest_count) as total'),
                ])
                ->get();

            foreach ($rows as $row) {
                $key = $row->tour_package_id.'|'.Carbon::parse($row->visit_date)->toDateString();
                $bookedMap[$key] = (int) $row->total;
            }
        }

        $rowsOut = [];
        foreach ($dates as $date) {
            $dateStr = $date->toDateString();
            foreach ($packages as $package) {
                $booked = $bookedMap[$package->id.'|'.$dateStr] ?? 0;
                $available = max(0, $package->daily_capacity - $booked);
                $utilization = $package->daily_capacity > 0
                    ? (int) round(($booked / $package->daily_capacity) * 100)
                    : 0;

                $rowsOut[] = [
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
            'rows' => $rowsOut,
            'hasPackages' => $packages->isNotEmpty(),
        ];
    }
}
