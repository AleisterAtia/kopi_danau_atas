<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class ExpirePendingBookings extends Command
{
    protected $signature = 'bookings:expire-pending';

    protected $description = 'Expire pending bookings older than 1 hour to free up quota';

    public function handle(): int
    {
        $expiredCount = Booking::where('status', 'pending')
            ->where('created_at', '<', now()->subHour())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expiredCount} pending booking(s).");

        return self::SUCCESS;
    }
}
