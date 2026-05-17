<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

/**
 * Mark paid/confirmed bookings as completed once their visit_date has
 * passed. This unblocks the review form on the user's "My Bookings" page.
 *
 * Scheduled to run daily at 23:55 (see routes/console.php).
 */
class AutoCompleteBookings extends Command
{
    protected $signature = 'bookings:auto-complete';
    protected $description = 'Auto-mark bookings as completed when their visit date has passed';

    public function handle(): int
    {
        $count = Booking::whereIn('status', ['paid', 'confirmed'])
            ->whereDate('visit_date', '<', today())
            ->update(['status' => 'completed']);

        $this->info("Auto-completed {$count} booking(s).");

        return self::SUCCESS;
    }
}
