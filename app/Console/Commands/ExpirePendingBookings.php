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
        // Per-model update (not a query-builder mass update) so
        // BookingObserver fires: enforces the transition is legal and writes
        // the "Booking status changed" audit log line.
        $bookings = Booking::where('status', 'pending')
            ->where('created_at', '<', now()->subHour())
            ->get();

        $bookings->each->update(['status' => 'expired']);

        $this->info("Expired {$bookings->count()} pending booking(s).");

        return self::SUCCESS;
    }
}
