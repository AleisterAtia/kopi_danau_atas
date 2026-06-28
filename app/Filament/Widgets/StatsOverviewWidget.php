<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverviewWidget extends BaseWidget
{
    /**
     * How long aggregate counters are cached. The dashboard does not need
     * second-by-second accuracy; a short TTL eliminates ~5 aggregate
     * queries per page load while still feeling fresh.
     */
    private const CACHE_TTL = 60; // seconds

    protected function getStats(): array
    {
        $data = Cache::remember('admin.stats.overview', self::CACHE_TTL, function () {
            return [
                'todayBookings' => Booking::whereDate('created_at', today())->count(),
                'monthBookings' => Booking::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'monthRevenue' => Payment::where('status', 'settlement')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('gross_amount'),
                'totalUsers' => User::where('role', 'user')->count(),
                'totalReviews' => Review::count(),
            ];
        });

        return [
            Stat::make('Bookings Today', $data['todayBookings'])
                ->description("{$data['monthBookings']} this month")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-ticket'),

            Stat::make('Revenue This Month', 'Rp '.number_format($data['monthRevenue'], 0, ',', '.'))
                ->description('From settled payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Registered Users', $data['totalUsers'])
                ->description('Total tourists')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->icon('heroicon-o-users'),

            // Reviews are auto-published (no moderation queue), so we surface
            // the total count instead of a "pending" backlog.
            Stat::make('Total Reviews', $data['totalReviews'])
                ->description('Published by tourists')
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color('success')
                ->icon('heroicon-o-star'),
        ];
    }
}
