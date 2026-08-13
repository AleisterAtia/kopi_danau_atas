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

    /**
     * @return array<array{0: string, 1: bool}>
     */
    public static function duplicateOrderIdMessages(): array
    {
        return [
            // Observed in production for the same condition — Midtrans
            // replies in whichever language the merchant account is set to.
            ['transaction_details.order_id has already been taken', true],
            ['transaction_details.order_id sudah digunakan', true],
            ['transaction_details.gross_amount is not equal to item_details', false],
        ];
    }

    #[DataProvider('duplicateOrderIdMessages')]
    public function test_duplicate_order_id_error_is_detected_regardless_of_message_language(string $message, bool $expected): void
    {
        $this->assertSame(
            $expected,
            app(MidtransService::class)->isDuplicateOrderIdError(new \Exception($message, 400))
        );
    }

    public function test_duplicate_order_id_error_requires_http_400(): void
    {
        $this->assertFalse(
            app(MidtransService::class)->isDuplicateOrderIdError(new \Exception('order_id has already been taken', 500))
        );
    }
}
