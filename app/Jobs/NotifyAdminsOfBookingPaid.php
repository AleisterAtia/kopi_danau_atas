<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\MidtransService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs the admin-facing side effects of a payment (in-panel notification,
 * WhatsApp via Fonnte, push notification) off the request/response cycle.
 *
 * These involve external HTTP calls (Fonnte, Reverb broadcast) that have
 * nothing to do with the paying customer — running them inline in
 * MidtransService::finalizePaidBooking() was adding several seconds to the
 * checkout redirect and the webhook response for no benefit to the customer.
 */
class NotifyAdminsOfBookingPaid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function handle(MidtransService $midtrans): void
    {
        $midtrans->notifyAdminsOfPayment($this->booking);
    }
}
