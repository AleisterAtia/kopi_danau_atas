<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Services\TicketCheckInService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the on-site e-ticket check-in: a valid paid ticket checks in
 * exactly once (idempotent on re-scan), unpaid tickets are refused, and a
 * forged/unknown token is rejected. The QR now carries an unguessable
 * ticket_token, so this is the only path to mark a guest as present.
 */
class TicketCheckInTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TicketCheckInService
    {
        return app(TicketCheckInService::class);
    }

    public function test_valid_paid_ticket_checks_in_once(): void
    {
        $staff = User::factory()->admin()->create();
        $booking = Booking::factory()->paid()->create();

        $outcome = $this->service()->checkIn($booking->ticket_token, $staff);

        $this->assertSame(TicketCheckInService::RESULT_SUCCESS, $outcome['result']);

        $booking->refresh();
        $this->assertNotNull($booking->checked_in_at);
        $this->assertSame($staff->id, $booking->checked_in_by);
    }

    public function test_second_scan_is_idempotent(): void
    {
        $staff = User::factory()->admin()->create();
        $booking = Booking::factory()->paid()->create();

        $first = $this->service()->checkIn($booking->ticket_token, $staff);
        $this->assertSame(TicketCheckInService::RESULT_SUCCESS, $first['result']);

        $checkedInAt = $booking->fresh()->checked_in_at;

        // A second scan of the same QR must NOT move the timestamp.
        $second = $this->service()->checkIn($booking->ticket_token, $staff);

        $this->assertSame(TicketCheckInService::RESULT_ALREADY, $second['result']);
        $this->assertEquals(
            $checkedInAt->toDateTimeString(),
            $booking->fresh()->checked_in_at->toDateTimeString()
        );
    }

    public function test_unpaid_ticket_is_not_eligible(): void
    {
        $booking = Booking::factory()->create(['status' => 'pending']);

        $outcome = $this->service()->checkIn($booking->ticket_token);

        $this->assertSame(TicketCheckInService::RESULT_NOT_ELIGIBLE, $outcome['result']);
        $this->assertNull($booking->fresh()->checked_in_at);
    }

    public function test_forged_or_unknown_token_is_rejected(): void
    {
        Booking::factory()->paid()->create();

        $outcome = $this->service()->checkIn('this-token-does-not-exist');

        $this->assertSame(TicketCheckInService::RESULT_NOT_FOUND, $outcome['result']);
        $this->assertNull($outcome['booking']);
    }

    public function test_empty_token_is_rejected(): void
    {
        $outcome = $this->service()->checkIn('   ');

        $this->assertSame(TicketCheckInService::RESULT_NOT_FOUND, $outcome['result']);
    }
}
