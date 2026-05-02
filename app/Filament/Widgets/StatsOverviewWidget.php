<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $todayBookings = Booking::whereDate('created_at', today())->count();
        $monthBookings = Booking::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $monthRevenue = Payment::where('status', 'settlement')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('gross_amount');

        $totalUsers = User::where('role', 'user')->count();
        $pendingReviews = Review::where('status', 'pending')->count();

        return [
            Stat::make('Bookings Today', $todayBookings)
                ->description("$monthBookings this month")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-ticket'),

            Stat::make('Revenue This Month', 'Rp ' . number_format($monthRevenue, 0, ',', '.'))
                ->description('From settled payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Registered Users', $totalUsers)
                ->description('Total tourists')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('Pending Reviews', $pendingReviews)
                ->description('Awaiting moderation')
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color($pendingReviews > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-star'),
        ];
    }
}
