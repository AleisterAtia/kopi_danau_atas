<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    /**
     * Allowed status transitions. Used by the BookingObserver and the
     * Filament EditBooking page to prevent illegal moves (e.g. moving a
     * `completed` booking back to `pending`).
     *
     * Format: from-status => [list of allowed to-statuses]
     */
    public const ALLOWED_TRANSITIONS = [
        'pending' => ['paid', 'cancelled', 'expired'],
        'paid' => ['confirmed', 'completed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled'],
        'completed' => [],            // terminal
        'cancelled' => [],            // terminal
        'expired' => [],            // terminal
    ];

    /**
     * How many times to retry generating a booking code on duplicate.
     */
    private const CODE_GENERATION_MAX_ATTEMPTS = 3;

    /**
     * Bookings that occupy quota: paid/confirmed/completed, plus pending
     * bookings created in the last hour (matches bookings:expire-pending's
     * cutoff, so mid-checkout users aren't double-booked while stale
     * pending rows are excluded). Single source of truth for this rule —
     * reused by TourPackage::getAvailableQuota/getBookedCount and by the
     * homepage/listing controllers' aggregate queries.
     */
    public function scopeOccupyingQuota(Builder $query): void
    {
        $query->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                    ->where('created_at', '>=', now()->subHour());
            });
    }

    protected $fillable = [
        'booking_code',
        'user_id',
        'tour_package_id',
        'visit_date',
        'guest_count',
        'guest_name',
        'guest_phone',
        'guest_email',
        'notes',
        'total_price',
        'status',
        'qr_code_path',
        'ticket_token',
        'checked_in_at',
        'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'total_price' => 'decimal:2',
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * Assign an unguessable ticket token to every new booking so the QR
     * e-ticket never has to encode the enumerable booking_code.
     */
    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->ticket_token)) {
                $booking->ticket_token = self::generateTicketToken();
            }
        });
    }

    /**
     * A high-entropy, URL-safe token embedded in the e-ticket QR.
     */
    public static function generateTicketToken(): string
    {
        return Str::random(64);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Only paid/confirmed/completed bookings represent a valid e-ticket.
     * pending/cancelled/expired tickets must be rejected at the gate.
     */
    public function isCheckInEligible(): bool
    {
        return in_array($this->status, ['paid', 'confirmed', 'completed'], true);
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    /**
     * Payload encoded into the e-ticket QR: a deep-link to the staff
     * check-in page carrying the unguessable token. Scanning it from a
     * device already logged into the admin panel prefills and validates
     * the ticket in one step.
     */
    public function ticketQrPayload(): string
    {
        return url('/admin/ticket-check-in').'?token='.urlencode((string) $this->ticket_token);
    }

    /**
     * Check whether a given status transition is allowed.
     *
     * Same-state ($from === $to) is treated as valid (no-op save).
     * Transitions to a status not present in ALLOWED_TRANSITIONS keys
     * are also rejected.
     */
    public static function canTransitionTo(?string $from, string $to): bool
    {
        if ($from === null || $from === $to) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? null;

        if ($allowed === null) {
            return false;
        }

        return in_array($to, $allowed, true);
    }

    /**
     * Generate a unique booking code atomically.
     *
     * Uses a serialised transaction with `lockForUpdate()` over the
     * existing rows for the day so that two concurrent INSERTs cannot
     * compute the same sequence number. Retries on the rare case of a
     * unique-constraint collision (which can still happen if the code
     * is generated outside of a transaction by older callers).
     */
    public static function generateBookingCode(string $date): string
    {
        $prefix = 'KDA-'.date('Ymd', strtotime($date));

        $attempts = 0;
        while (true) {
            $attempts++;
            try {
                return DB::transaction(function () use ($prefix) {
                    $lastBooking = static::where('booking_code', 'like', $prefix.'%')
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->first();

                    $sequence = 1;
                    if ($lastBooking) {
                        $lastSequence = (int) substr($lastBooking->booking_code, -5);
                        $sequence = $lastSequence + 1;
                    }

                    return $prefix.'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
                });
            } catch (QueryException $e) {
                if ($attempts >= self::CODE_GENERATION_MAX_ATTEMPTS) {
                    throw $e;
                }
                // brief jitter before retry
                usleep(random_int(10_000, 50_000));
            }
        }
    }
}
