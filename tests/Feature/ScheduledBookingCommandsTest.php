<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * bookings:expire-pending and bookings:auto-complete must update bookings
 * one at a time (not via a query-builder mass update) so
 * BookingObserver::updated() fires and writes its "Booking status changed"
 * audit log line for every transition these commands make.
 */
class ScheduledBookingCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expire_pending_logs_a_status_change_per_booking(): void
    {
        Log::spy();

        $booking = Booking::factory()->create([
            'status' => 'pending',
            'created_at' => now()->subHours(2),
        ]);

        $this->artisan('bookings:expire-pending')->assertSuccessful();

        $this->assertSame('expired', $booking->fresh()->status);
        Log::shouldHaveReceived('info')
            ->with('Booking status changed', \Mockery::on(
                fn ($context) => $context['booking_id'] === $booking->id
                    && $context['from'] === 'pending'
                    && $context['to'] === 'expired'
            ))
            ->once();
    }

    public function test_auto_complete_logs_a_status_change_per_booking(): void
    {
        Log::spy();

        $booking = Booking::factory()->create([
            'status' => 'paid',
            'visit_date' => today()->subDay(),
        ]);

        $this->artisan('bookings:auto-complete')->assertSuccessful();

        $this->assertSame('completed', $booking->fresh()->status);
        Log::shouldHaveReceived('info')
            ->with('Booking status changed', \Mockery::on(
                fn ($context) => $context['booking_id'] === $booking->id
                    && $context['from'] === 'paid'
                    && $context['to'] === 'completed'
            ))
            ->once();
    }
}
