<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * Guards against issuing a new Snap token for a booking that can no longer
 * be honored. A stale checkout tab replaying POST /booking/{id}/pay after
 * bookings:expire-pending already reclaimed the booking must not be able to
 * take money for it.
 */
class PaymentSnapTokenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<array{0: string}>
     */
    public static function nonPayableStatuses(): array
    {
        return [
            ['expired'],
            ['cancelled'],
            ['paid'],
            ['confirmed'],
            ['completed'],
        ];
    }

    #[DataProvider('nonPayableStatuses')]
    public function test_snap_token_is_refused_for_non_pending_booking(string $status): void
    {
        $booking = Booking::factory()->create(['status' => $status]);

        $this->expectException(RuntimeException::class);

        app(MidtransService::class)->createSnapToken($booking);
    }
}
