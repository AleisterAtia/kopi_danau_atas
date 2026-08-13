<?php

namespace App\Services;

use App\Filament\Resources\BookingResource;
use App\Jobs\NotifyAdminsOfBookingPaid;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\BookingPaidPushNotification;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create (or refresh) a Snap token for a booking.
     *
     * Behaviour:
     * - Only a booking still in `pending` may be paid. Anything else
     *   (paid/confirmed/completed → already paid; expired/cancelled →
     *   dead, quota may already be resold) → reject. A stale checkout
     *   tab replaying this request after the booking died must not be
     *   able to take money for a booking that will never be honored.
     * - If a Payment row already exists with status `pending` → reuse
     *   its `midtrans_order_id` so the existing webhook
     *   correlation continues to work. We still ask Midtrans for a
     *   fresh Snap token (Snap tokens have a short TTL).
     * - Otherwise create a new Payment row with a brand-new order_id.
     */
    public function createSnapToken(Booking $booking): string
    {
        $booking->load(['user', 'tourPackage', 'payment']);

        if ($booking->status !== 'pending') {
            throw new RuntimeException('Booking ini sudah tidak dapat dibayar (status: '.$booking->status.').');
        }

        $existingPayment = $booking->payment;

        // Reuse the existing order_id when we still have a pending payment.
        // This keeps webhook correlation stable across "Pay Again" clicks.
        $orderId = ($existingPayment && $existingPayment->status === 'pending' && $existingPayment->midtrans_order_id)
            ? $existingPayment->midtrans_order_id
            : $this->newOrderId($booking);

        try {
            $snapToken = Snap::getSnapToken($this->buildSnapParams($booking, $orderId));
        } catch (\Exception $e) {
            // Midtrans locks an order_id the moment a payment channel is
            // actually selected under it (e.g. a bank VA gets issued) — a
            // later "Pay Again" reusing that same order_id is then rejected
            // outright, even though our own Payment row still shows the
            // booking as pending. Mint a fresh order_id and retry once
            // instead of dead-ending the customer with no way to pay short
            // of cancelling the booking.
            //
            // Matched on the "order_id" field name rather than the rest of
            // the message: Midtrans returns this error in whatever language
            // the merchant account is set to ("order_id has already been
            // taken" vs "order_id sudah digunakan" have both been observed
            // in production for the exact same condition), so pinning to
            // one language's exact phrase silently stops catching it the
            // moment the account's locale differs.
            if (! $this->isDuplicateOrderIdError($e)) {
                throw $e;
            }

            $orderId = $this->newOrderId($booking);
            $snapToken = Snap::getSnapToken($this->buildSnapParams($booking, $orderId));
        }

        Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'midtrans_order_id' => $orderId,
                'snap_token' => $snapToken,
                'gross_amount' => round($booking->total_price, 2),
                'status' => 'pending',
            ]
        );

        Log::info('Midtrans Snap token created', [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'order_id' => $orderId,
            'reused' => $existingPayment && $existingPayment->midtrans_order_id === $orderId,
        ]);

        return $snapToken;
    }

    protected function newOrderId(Booking $booking): string
    {
        // uniqid(), not just time(): two clicks landing in the same second
        // (a fast double-click, or a retry immediately following a failed
        // first attempt) must never mint the same "fresh" order_id, or the
        // retry below collides with itself.
        return 'KDA-'.$booking->id.'-'.time().'-'.uniqid();
    }

    public function isDuplicateOrderIdError(\Exception $e): bool
    {
        return $e->getCode() === 400 && str_contains($e->getMessage(), 'order_id');
    }

    protected function buildSnapParams(Booking $booking, string $orderId): array
    {
        return [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($booking->total_price),
            ],
            'customer_details' => [
                'first_name' => $booking->guest_name ?? $booking->user->name,
                'email' => $booking->guest_email ?? $booking->user->email,
                'phone' => $booking->guest_phone ?? $booking->user->phone ?? '',
            ],
            // Single line item at quantity 1, priced at the booking's frozen
            // total_price (not the package's live price) — guarantees this
            // always sums to exactly gross_amount, even if an admin edits the
            // package price while this booking sits pending.
            'item_details' => [
                [
                    'id' => $booking->tour_package_id,
                    'price' => (int) round($booking->total_price),
                    'quantity' => 1,
                    'name' => substr($booking->tourPackage->name, 0, 50),
                ],
            ],
            'callbacks' => [
                'finish' => url('/booking/'.$booking->id),
            ],
            // Kill the payment window exactly when bookings:expire-pending kills
            // the booking (1h after created_at), so a bank-transfer VA — valid
            // 24h by default — can never outlive the booking it pays for and
            // land money on an already-expired row. Counted from created_at, not
            // now(), so "pay again" clicks don't extend the window.
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => $this->minutesLeftToPay($booking),
            ],
        ];
    }

    /**
     * Minutes a booking still has before bookings:expire-pending reclaims it.
     *
     * Floored at 1 because Midtrans rejects a zero/negative duration; a booking
     * that is already past the window simply gets the shortest window Midtrans
     * accepts, and the late-payment guard in handleNotification() catches
     * whatever slips through that last minute.
     */
    protected function minutesLeftToPay(Booking $booking): int
    {
        // ponytail: the 1h window is duplicated in ExpirePendingBookings and
        // TourPackage::getAvailableQuota — promote to one Booking constant if a
        // third rule ever needs it.
        $elapsed = (int) $booking->created_at->diffInMinutes(now());

        return max(1, 60 - $elapsed);
    }

    /**
     * Verify webhook signature from Midtrans.
     */
    public function verifySignature(array $notification): bool
    {
        $signatureKey = hash('sha512',
            ($notification['order_id'] ?? '').
            ($notification['status_code'] ?? '').
            ($notification['gross_amount'] ?? '').
            config('midtrans.server_key')
        );

        return hash_equals($signatureKey, (string) ($notification['signature_key'] ?? ''));
    }

    /**
     * Handle a notification payload (either from the webhook or from
     * the client-side polling fallback).
     *
     * Idempotency: if Midtrans replays a notification we have already
     * processed (same `transaction_id` for the same payment), we
     * short-circuit so QR / email / status updates do not run twice.
     */
    public function handleNotification(array $notification): void
    {
        $orderId = $notification['order_id'] ?? null;

        if (! $orderId) {
            Log::warning('Midtrans notification without order_id', $notification);

            return;
        }

        $payment = Payment::where('midtrans_order_id', $orderId)->first();

        if (! $payment) {
            Log::warning('Midtrans notification for unknown order_id', [
                'order_id' => $orderId,
            ]);

            return;
        }

        $incomingTransactionId = $notification['transaction_id'] ?? null;
        $incomingStatus = $notification['transaction_status'] ?? '';

        // Idempotency: same transaction_id + same status already processed.
        if (
            $incomingTransactionId
            && $payment->midtrans_transaction_id === $incomingTransactionId
            && $payment->status === $this->mapTransactionStatus($incomingStatus)
        ) {
            Log::info('Midtrans notification ignored (already processed)', [
                'order_id' => $orderId,
                'transaction_id' => $incomingTransactionId,
                'status' => $incomingStatus,
            ]);

            return;
        }

        // Payment row and booking row must move together or not at all. Without
        // this transaction the payment update commits, the booking update throws
        // on an illegal transition, and Midtrans' retry then hits the
        // idempotency guard above and gets a 200 — silently burying the failure.
        $shouldFinalize = DB::transaction(function () use ($payment, $notification, $incomingTransactionId, $incomingStatus, $orderId) {
            $payment->update([
                'midtrans_transaction_id' => $incomingTransactionId,
                'payment_type' => $notification['payment_type'] ?? null,
                'midtrans_response' => $notification,
            ]);

            switch ($incomingStatus) {
                case 'capture':
                case 'settlement':
                    // Idempotency: only fire post-payment side-effects (QR + email)
                    // when this booking transitions INTO paid for the first time.
                    $wasAlreadyPaid = in_array(
                        $payment->booking->status,
                        ['paid', 'confirmed', 'completed'],
                        true
                    );

                    $payment->update(['status' => 'settlement', 'paid_at' => $payment->paid_at ?? now()]);

                    return $this->applyBookingStatus($payment, 'paid') && ! $wasAlreadyPaid;

                case 'expire':
                    // A customer can reopen Snap and retry with a different
                    // payment method under the same order_id, producing a new
                    // transaction_id per attempt. If an *older* attempt's
                    // expire/cancel/deny notification is delivered late — after
                    // a *newer* attempt already settled — it must not undo the
                    // money that already came in. $payment->status is the
                    // committed DB state, so this check is race-safe regardless
                    // of notification delivery order.
                    if ($payment->status === 'settlement') {
                        Log::warning('Ignoring stale expire notification for an already-settled payment', [
                            'order_id' => $orderId,
                            'transaction_id' => $incomingTransactionId,
                        ]);
                        break;
                    }

                    $payment->update(['status' => 'expire']);
                    $this->applyBookingStatus($payment, 'expired');
                    break;

                case 'cancel':
                case 'deny':
                    if ($payment->status === 'settlement') {
                        Log::warning('Ignoring stale cancel/deny notification for an already-settled payment', [
                            'order_id' => $orderId,
                            'transaction_id' => $incomingTransactionId,
                        ]);
                        break;
                    }

                    $payment->update(['status' => $incomingStatus]);
                    $this->applyBookingStatus($payment, 'cancelled');
                    break;

                case 'pending':
                    $payment->update(['status' => 'pending']);
                    break;

                case 'refund':
                case 'partial_refund':
                    $payment->update(['status' => 'refund']);
                    $this->applyBookingStatus($payment, 'cancelled');
                    break;

                default:
                    Log::warning('Midtrans notification with unknown status', [
                        'order_id' => $orderId,
                        'status' => $incomingStatus,
                    ]);
            }

            return false;
        });

        // Runs after commit: QR writes a file and mail queues a job, neither of
        // which should happen for a transaction that ends up rolled back.
        if ($shouldFinalize) {
            $this->finalizePaidBooking($payment->booking->fresh());
        }

        Log::info('Midtrans notification processed', [
            'order_id' => $orderId,
            'transaction_id' => $incomingTransactionId,
            'status' => $incomingStatus,
        ]);
    }

    /**
     * Apply a booking status coming from Midtrans, refusing illegal transitions
     * instead of letting them throw.
     *
     * A notification is a statement about money that already moved — it is never
     * wrong, and it can never be "retried away". If it disagrees with the
     * booking's state (almost always because bookings:expire-pending reclaimed
     * the booking while the customer was paying a still-valid VA), throwing
     * would abort the webhook and leave Midtrans retrying into the idempotency
     * guard. So we record the payment truthfully, leave the booking alone, and
     * shout for a human — the same manual-reconciliation stance RefundService
     * documents for refunds.
     *
     * @return bool whether the booking actually moved
     */
    protected function applyBookingStatus(Payment $payment, string $status): bool
    {
        $booking = $payment->booking;

        if (Booking::canTransitionTo($booking->status, $status)) {
            $booking->update(['status' => $status]);

            return true;
        }

        Log::critical('Midtrans notification could not be applied to booking', [
            'booking_code' => $booking->booking_code,
            'booking_status' => $booking->status,
            'attempted_status' => $status,
            'order_id' => $payment->midtrans_order_id,
            'transaction_id' => $payment->midtrans_transaction_id,
            'gross_amount' => $payment->gross_amount,
            'action_required' => $status === 'paid'
                ? 'Uang diterima untuk booking yang sudah mati — refund manual lewat dashboard Midtrans.'
                : 'Status pembayaran & booking tidak sinkron — rekonsiliasi manual.',
        ]);

        return false;
    }

    /**
     * Map a Midtrans transaction_status value to our Payment.status
     * field. Only used for idempotency comparisons.
     */
    protected function mapTransactionStatus(string $midtransStatus): string
    {
        return match ($midtransStatus) {
            'capture', 'settlement' => 'settlement',
            'refund', 'partial_refund' => 'refund',
            default => $midtransStatus,
        };
    }

    /**
     * Run side-effects that should happen exactly once when a booking
     * transitions to paid: generate QR code + send confirmation email.
     *
     * Errors are logged but never thrown — we don't want a flaky SMTP
     * server to mark the webhook handler as failed (which would cause
     * Midtrans to retry and double-update the booking).
     */
    protected function finalizePaidBooking(Booking $booking): void
    {
        try {
            $qrPath = app(QrCodeService::class)->generate($booking);
            $booking->update(['qr_code_path' => $qrPath]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate QR code for booking '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }

        try {
            Mail::to($booking->guest_email ?? $booking->user->email)
                ->queue(new BookingConfirmation($booking->fresh(), app()->getLocale()));
        } catch (\Throwable $e) {
            Log::error('Failed to queue booking confirmation email for '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }

        try {
            // Queued: WhatsApp (Fonnte) and the realtime broadcast are
            // external HTTP calls with nothing to do with the paying
            // customer — running them inline here was adding several
            // seconds to the checkout redirect / webhook response.
            NotifyAdminsOfBookingPaid::dispatch($booking);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch admin payment notification job for booking '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Alert admins the moment a booking is actually paid (not when merely
     * placed) — this is the point at which staff need to act (confirm the
     * booking). Sent to the database (Filament bell) + broadcast over
     * websockets (Reverb) for an instant in-panel toast, to WhatsApp for
     * staff not looking at the panel, and as a native OS push notification
     * for staff who opted in via "Aktifkan Notifikasi" but have no tab open.
     */
    public function notifyAdminsOfPayment(Booking $booking): void
    {
        $admins = User::where('role', 'admin')->get();

        // Each channel below gets its own try/catch: Reverb being down (or any
        // other channel failing) must not stop the remaining channels from
        // firing — WhatsApp and push are exactly the "admin isn't looking at
        // the panel" fallbacks, so they especially can't depend on the panel's
        // own realtime channel working.
        try {
            Notification::make()
                ->title('Pembayaran diterima')
                ->body("{$booking->booking_code} — {$booking->guest_name} ({$booking->guest_count} orang) sudah bayar, menunggu konfirmasi.")
                ->icon('heroicon-o-banknotes')
                ->iconColor('success')
                ->actions([
                    Action::make('view')
                        ->label('Lihat')
                        ->url(BookingResource::getUrl('edit', ['record' => $booking])),
                ])
                ->sendToDatabase($admins, isEventDispatched: true)
                ->broadcast($admins);
        } catch (\Throwable $e) {
            Log::error('Failed to send in-panel notification for booking '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }

        $this->notifyAdminsOfPaymentViaWhatsapp($admins, $booking);

        try {
            LaravelNotification::send($admins, new BookingPaidPushNotification($booking));
        } catch (\Throwable $e) {
            Log::error('Failed to send push notification to admins for booking '.$booking->booking_code, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * WhatsApp leg of notifyAdminsOfPayment(), via the Fonnte gateway.
     * No-op when FONNTE_TOKEN is unset (feature off, same fallback stance as
     * GEMINI_API_KEY) — one admin's missing phone or a failed send is logged
     * and skipped rather than blocking the others.
     */
    protected function notifyAdminsOfPaymentViaWhatsapp(Collection $admins, Booking $booking): void
    {
        if (blank(config('services.fonnte.token'))) {
            return;
        }

        $message = "Booking baru dibayar!\n\n"
            ."Kode: {$booking->booking_code}\n"
            ."Nama: {$booking->guest_name}\n"
            ."Jumlah tamu: {$booking->guest_count}\n\n"
            .'Konfirmasi di: '.BookingResource::getUrl('edit', ['record' => $booking]);

        foreach ($admins as $admin) {
            if (blank($admin->phone)) {
                continue;
            }

            try {
                $response = Http::withHeaders(['Authorization' => config('services.fonnte.token')])
                    ->asForm()
                    ->post('https://api.fonnte.com/send', [
                        'target' => $this->toFonnteTarget($admin->phone),
                        'message' => $message,
                    ]);

                // Http only throws on network-level failure (timeout, DNS...).
                // Fonnte returning 401/403 (e.g. expired token) is a normal
                // HTTP response, not an exception — must be checked explicitly
                // or a dead token fails completely silently.
                if ($response->failed()) {
                    Log::error('Fonnte rejected WhatsApp payment notification to admin '.$admin->id, [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send WhatsApp payment notification to admin '.$admin->id, [
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Fonnte expects the country code — a local "08xx" number never reaches
     * the device. Storage keeps whatever an admin typed into their profile.
     */
    protected function toFonnteTarget(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}
