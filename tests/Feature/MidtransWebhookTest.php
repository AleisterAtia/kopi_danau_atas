<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Guards the most security-critical path in the system: the Midtrans
 * payment notification handler. Verifies signature enforcement, correct
 * status mapping, and — crucially — that paid side-effects (e-ticket +
 * confirmation email) fire exactly once even when Midtrans replays a
 * notification.
 */
class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'SB-Mid-server-test-key';

    private const WEBHOOK_URL = '/api/midtrans/notification';

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => self::SERVER_KEY]);
        // QR generation writes to the public disk; isolate it from real storage.
        Storage::fake('public');
    }

    /**
     * Create a pending booking with a matching pending payment row.
     */
    private function pendingPayment(float $amount = 250000): Payment
    {
        $booking = Booking::factory()->create([
            'status' => 'pending',
            'total_price' => $amount,
        ]);

        return Payment::factory()->create([
            'booking_id' => $booking->id,
            'status' => 'pending',
            'gross_amount' => $amount,
        ]);
    }

    /**
     * Build a notification payload carrying a valid Midtrans signature.
     */
    private function notificationFor(Payment $payment, string $transactionStatus, array $overrides = []): array
    {
        $orderId = $payment->midtrans_order_id;
        $statusCode = $overrides['status_code'] ?? '200';
        $grossAmount = $overrides['gross_amount'] ?? number_format((float) $payment->gross_amount, 2, '.', '');

        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.self::SERVER_KEY);

        return array_merge([
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'transaction_id' => 'txn-'.$orderId,
            'payment_type' => 'bank_transfer',
            'signature_key' => $signature,
        ], $overrides);
    }

    public function test_settlement_marks_booking_paid_and_sends_confirmation_once(): void
    {
        Mail::fake();
        $payment = $this->pendingPayment();

        $this->postJson(self::WEBHOOK_URL, $this->notificationFor($payment, 'settlement'))
            ->assertOk();

        $payment->refresh();
        $this->assertSame('settlement', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame('paid', $payment->booking->status);
        Mail::assertQueued(BookingConfirmation::class, 1);
    }

    public function test_duplicate_settlement_notification_is_idempotent(): void
    {
        Mail::fake();
        $payment = $this->pendingPayment();
        $notification = $this->notificationFor($payment, 'settlement');

        $service = app(MidtransService::class);
        $service->handleNotification($notification);
        $service->handleNotification($notification); // Midtrans replay

        // Confirmation email + e-ticket must be produced exactly once.
        Mail::assertQueued(BookingConfirmation::class, 1);
        $this->assertSame('paid', $payment->booking->fresh()->status);
    }

    public function test_invalid_signature_is_rejected_and_booking_unchanged(): void
    {
        $payment = $this->pendingPayment();
        $notification = $this->notificationFor($payment, 'settlement', [
            'signature_key' => 'forged-signature',
        ]);

        $this->postJson(self::WEBHOOK_URL, $notification)->assertStatus(403);

        $this->assertSame('pending', $payment->booking->fresh()->status);
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_expire_notification_marks_booking_expired(): void
    {
        $payment = $this->pendingPayment();

        $this->postJson(self::WEBHOOK_URL, $this->notificationFor($payment, 'expire'))->assertOk();

        $this->assertSame('expired', $payment->booking->fresh()->status);
        $this->assertSame('expire', $payment->fresh()->status);
    }

    public function test_cancel_notification_marks_booking_cancelled(): void
    {
        $payment = $this->pendingPayment();

        $this->postJson(self::WEBHOOK_URL, $this->notificationFor($payment, 'cancel'))->assertOk();

        $this->assertSame('cancelled', $payment->booking->fresh()->status);
        $this->assertSame('cancel', $payment->fresh()->status);
    }

    public function test_notification_for_unknown_order_is_ignored(): void
    {
        // Valid signature, but no Payment row matches this order_id.
        $orderId = 'KDA-999-nonexistent';
        $statusCode = '200';
        $grossAmount = '250000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.self::SERVER_KEY);

        $this->postJson(self::WEBHOOK_URL, [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'signature_key' => $signature,
        ])->assertOk();

        $this->assertDatabaseCount('bookings', 0);
    }
}
