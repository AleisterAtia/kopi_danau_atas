<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\BookingPaidPushNotification;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
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
        // Never let a test hit the real Fonnte API.
        Http::fake();
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

    /**
     * The customer pays a still-valid VA after bookings:expire-pending already
     * reclaimed the booking. Money is real, so the payment must be recorded and
     * the webhook must ack — but the dead booking must not be resurrected (its
     * quota was released and may have been resold), and no ticket may go out.
     */
    public function test_late_settlement_on_an_expired_booking_is_recorded_without_issuing_a_ticket(): void
    {
        Mail::fake();
        $payment = $this->pendingPayment();

        $this->travel(2)->hours();
        $this->artisan('bookings:expire-pending')->assertSuccessful();
        $this->assertSame('expired', $payment->booking->fresh()->status);

        $notification = $this->notificationFor($payment, 'settlement');

        // Must ack, or Midtrans retries into the idempotency guard and the
        // failure is buried behind a 200.
        $this->postJson(self::WEBHOOK_URL, $notification)->assertOk();

        $payment->refresh();
        $this->assertSame('settlement', $payment->status, 'payment must record the money that moved');
        $this->assertNotNull($payment->paid_at);
        $this->assertSame('expired', $payment->booking->fresh()->status, 'dead booking must not be revived');
        $this->assertNull($payment->booking->fresh()->qr_code_path);
        Mail::assertNothingQueued();

        // A retry must stay consistent, not flip anything.
        $this->postJson(self::WEBHOOK_URL, $notification)->assertOk();
        $this->assertSame('expired', $payment->booking->fresh()->status);
        Mail::assertNothingQueued();
    }

    /**
     * Guards the atomicity that makes the case above safe: payment and booking
     * must never disagree because one committed and the other threw.
     */
    public function test_refund_notification_on_a_completed_booking_does_not_split_payment_and_booking(): void
    {
        $payment = $this->pendingPayment();
        $payment->booking->update(['status' => 'paid']);
        $payment->booking->update(['status' => 'completed']);

        $this->postJson(self::WEBHOOK_URL, $this->notificationFor($payment, 'refund'))->assertOk();

        // completed is terminal, so the booking stays put — but the refund is
        // still on the payment record for the admin to reconcile.
        $this->assertSame('refund', $payment->fresh()->status);
        $this->assertSame('completed', $payment->booking->fresh()->status);
    }

    /**
     * A customer can reopen Snap and retry with a different payment method
     * under the same order_id — each attempt gets its own transaction_id.
     * If the *first* (abandoned) attempt's cancel notification is delivered
     * late, after the *second* attempt already settled, it must not undo the
     * payment: the money already moved.
     */
    public function test_stale_cancel_notification_does_not_unpay_an_already_settled_booking(): void
    {
        Mail::fake();
        $payment = $this->pendingPayment();

        $settleNotification = $this->notificationFor($payment, 'settlement', [
            'transaction_id' => 'txn-attempt-2',
        ]);
        $this->postJson(self::WEBHOOK_URL, $settleNotification)->assertOk();
        $this->assertSame('paid', $payment->booking->fresh()->status);

        $staleCancelNotification = $this->notificationFor($payment, 'cancel', [
            'transaction_id' => 'txn-attempt-1',
        ]);
        $this->postJson(self::WEBHOOK_URL, $staleCancelNotification)->assertOk();

        $payment->refresh();
        $this->assertSame('settlement', $payment->status, 'stale cancel must not overwrite a settled payment');
        $this->assertSame('paid', $payment->booking->fresh()->status, 'stale cancel must not unpay the booking');
    }

    public function test_settlement_sends_whatsapp_notification_to_admins_with_a_phone_number(): void
    {
        Mail::fake();
        config(['services.fonnte.token' => 'test-token']);
        User::factory()->admin()->create(['phone' => '081234567890']);
        User::factory()->admin()->create(['phone' => null]); // must be skipped, not error

        $payment = $this->pendingPayment();

        $this->postJson(self::WEBHOOK_URL, $this->notificationFor($payment, 'settlement'))->assertOk();

        // Booking::factory() also creates a TourPackage, which auto-translates
        // via Gemini on save — scope to the Fonnte endpoint so that unrelated
        // HTTP call (also captured by the blanket Http::fake()) isn't counted.
        $this->assertCount(1, Http::recorded(fn ($request) => str_contains($request->url(), 'fonnte.com')));
        Http::assertSent(function ($request) use ($payment) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'test-token')
                && $request['target'] === '6281234567890'
                && str_contains($request['message'], $payment->booking->booking_code);
        });
    }

    public function test_settlement_skips_whatsapp_notification_when_fonnte_token_is_unset(): void
    {
        Mail::fake();
        config(['services.fonnte.token' => null]);
        User::factory()->admin()->create(['phone' => '081234567890']);

        $payment = $this->pendingPayment();

        $this->postJson(self::WEBHOOK_URL, $this->notificationFor($payment, 'settlement'))->assertOk();

        $this->assertCount(0, Http::recorded(fn ($request) => str_contains($request->url(), 'fonnte.com')));
    }

    public function test_settlement_sends_push_notification_to_admins(): void
    {
        Mail::fake();
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $payment = $this->pendingPayment();

        $this->postJson(self::WEBHOOK_URL, $this->notificationFor($payment, 'settlement'))->assertOk();

        Notification::assertSentTo($admin, BookingPaidPushNotification::class);
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
